<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scriptlodge\Bankofmaldives\Gateway\Validator;

use Magento\Framework\App\Request;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Scriptlodge\Bankofmaldives\Helper\Data;

//use Scriptlodge\Bankofmaldives\Gateway\Request\HtmlRedirect\OrderDataBuilder;

class ResponseValidator extends AbstractValidator
{

    /**
     * @var Request\Http
     */
    private $request;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;


    protected $bmlHelper;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param Request\Http $request
     * @param RemoteAddress $remoteAddress
     * @param ConfigInterface $config
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        Request\Http $request,
        RemoteAddress $remoteAddress,
        ConfigInterface $config,
        OrderRepositoryInterface $orderRepository,
        Data $bmlHelper
    )
    {
        parent::__construct($resultFactory);

        $this->request = $request;
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->remoteAddress = $remoteAddress;
        $this->bmlHelper = $bmlHelper;
    }

    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(array $validationSubject)
    {
        $requestData = $this->bmlHelper->getResponseValue();
        $orderId = null;
        $explanation = "You cancel the order";

exit('res');
        $orderIsNotFound = function () {
            $result = true;
            try {
                if (!empty($requestData) && isset($requestData['order_id'])) {
                    $orderId = $requestData['order_id'];
                    $order = $this->orderRepository->get($this->request->getPost($orderId));
                } else {
                    $result = false;
                }

            } catch (NotFoundException $e) {
                $result = false;
            }

            return [
                $result,
                'Order is not found.'
            ];
        };
        $cause = $_explanation = $invoice_id = "";

        if (!empty($requestData['result']) && $requestData['result'] == 'success') {
            $invoiceResponse = json_decode($requestData['response']);
            $invoice_id = $invoiceResponse->data->invoice_id;
        }


        if ((!empty($validationSubject['response']['status']) && $validationSubject['response']['status'] == 'ACCEPTED') && (!empty($validationSubject['response']['invoice']) && $invoice_id == $validationSubject['response']['invoice'])) {
            return $this->createResult(true);
        } else {
            return $this->createResult(false);
        }
    }

}
