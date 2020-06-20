<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scriptlodge\Bankofmaldives\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Scriptlodge\Bankofmaldives\Gateway\Validator\DecisionValidator;
use Scriptlodge\Bankofmaldives\Helper\Data;
use Scriptlodge\Bankofmaldives\Model\BankofmaldivesFactory;

/**
 * Class ResponseCommand
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResponseCommand implements CommandInterface
{
    const ACCEPT_COMMAND = 'accept_command';

    const CANCEL_COMMAND = 'cancel_command';

    /**
     * Transaction result codes map onto commands
     *
     * @var array
     */
    static private $commandsMap = [
        'C' => self::CANCEL_COMMAND,
        'Y' => self::ACCEPT_COMMAND
    ];


    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var Logger
     */
    private $logger;

    protected $bmlHelper;

    /**
     * @var OrderSender
     */
    private $orderSender;


    /**
     * @var ConfigInterface
     */
    private $config;

    protected $bankofmaldivesFactory;


    /**
     * @param CommandPoolInterface $commandPool
     * @param ValidatorInterface $validator
     * @param OrderRepositoryInterface $orderRepository
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param Logger $logger
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        ValidatorInterface $validator,
        OrderRepositoryInterface $orderRepository,
        OrderInterface $order,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        Logger $logger,
        OrderSender $orderSender,
        Data $bmlHelper,
        BankofmaldivesFactory $bankofmaldivesFactory,
        ConfigInterface $config
    )
    {
        $this->commandPool = $commandPool;
        $this->validator = $validator;
        $this->orderRepository = $orderRepository;
        $this->order = $order;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->logger = $logger;
        $this->bmlHelper = $bmlHelper;
        $this->orderSender = $orderSender;
        $this->config = $config;
        $this->bankofmaldivesFactory = $bankofmaldivesFactory;
    }

    /**
     * @param array $commandSubject
     *
     * @return void
     * @throws CommandException
     */
    public function execute(array $commandSubject)
    {
        $this->logger->debug($commandSubject);
        $transStatus = null;
        $order = null;
        $orderId = null;

        $response = SubjectReader::readResponse($commandSubject);
        $orderId = isset($response['OrderID']) ? $response['OrderID'] : "";

        if ($orderId) {
            $order = $this->order->loadByIncrementId($orderId);
        }

        if (isset($response['ResponseCode']) && $response['ResponseCode'] == 1) {
            $merchantId = isset($response['MerID']) ? $response['MerID'] : "";
            $acquirerId = isset($response['AcqID']) ? $response['AcqID'] : "";
            $signature = isset($response['Signature']) ? $response['Signature'] : "";
            $responseCode = isset($response['ResponseCode']) ? $response['ResponseCode'] : "";
            $reasonCode = isset($response['ReasonCode']) ? $response['ReasonCode'] : "";
            $_generatedSignature="";
            $password = $this->getApiPassword($order);
            if($password && $merchantId && $acquirerId && $orderId && $responseCode && $reasonCode){
                $_generatedSignature=   $this->getSignature($password, $merchantId, $acquirerId, $orderId, $responseCode, $reasonCode);
            }

            if ($_generatedSignature == $signature) {
                $transStatus = 'Y';
            } else {
                $transStatus = 'F';
                $comment = "Signature not Matched";
                $response['comment']=$response;
            }
        } elseif (isset($response['ResponseCode']) && $response['ResponseCode'] == 2) {
            $transStatus = 'C';
        } elseif (isset($response['ResponseCode']) && $response['ResponseCode'] == 3) {
            $transStatus = 'C';
        } else {
            $transStatus = 'C';
        }

        if ($orderId) {
            #$order = $this->orderRepository->get((int)$orderId);
          //  $order = $this->order->loadByIncrementId($orderId);
            $actionCommandSubject = [
                'response' => $response,
                'payment' => $this->paymentDataObjectFactory->create(
                    $order->getPayment()
                )
            ];
            if ($transStatus) {
                $command = $this->commandPool->get(
                    self::$commandsMap[$transStatus]
                );
                $command->execute($actionCommandSubject);

                if ($transStatus == 'Y') {
                    $this->orderSender->send($order);
                }
            }
        }
    }


    public function getSignature($password, $merID, $acqID, $orderId, $responseCode, $reasonCode)
    {

        $signString = $password . $merID . $acqID . $orderId . $responseCode . $reasonCode;
        $signature = base64_encode(sha1($signString, true));

        return $signature;
    }

    protected function getApiPassword($order)
    {
        return $this->bmlHelper->getConfigValue('password', $order->getStoreId(), true);
    }

}
