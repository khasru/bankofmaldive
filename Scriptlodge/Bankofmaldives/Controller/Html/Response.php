<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scriptlodge\Bankofmaldives\Controller\Html;

use Magento\Framework\App\Request;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Scriptlodge\Bankofmaldives\Gateway\Command\ResponseCommand;
use Psr\Log\LoggerInterface;

/**
 * Displays message and redirect to the ResultController with appropriate parameter
 *
 * Class Response
 */
class Response extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /**
     * @var ResponseCommand
     */
    private $command;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private static $transStatusSuccess = 'Y';

    /**
     * @var string
     */
    private static $transStatusCancel = 'C';

    private static $transStatusFailure = 'F';

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * @param Context $context
     * @param ResponseCommand $command
     * @param LoggerInterface $logger
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        Context $context,
        ResponseCommand $command,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger,
        LayoutFactory $layoutFactory
    )
    {
        parent::__construct($context);

        $this->command = $command;
        $this->layoutFactory = $layoutFactory;
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        /* print_r($_REQUEST);
         exit('responsen');*/

        $params = $this->getRequest()->getParams();
        $resultLayout = $this->layoutFactory->create();
        $resultLayout->addDefaultHandle();
        $processor = $resultLayout->getLayout()->getUpdate();

        //print_r($params);

        /* $params = array('ResponseCode' => 2,
             'ReasonCode' => 300,
             'ReasonCodeDesc' => "Transaction not approved : 79",
             'OrderID' => "000001987",
             'MerID' => 9809687882,
             'CavvEciInd' => "N",
             'transStatus' => 'C',
             'CAVV' => "",
             'ECI' => "07"
         );*/

       /* $params = array (
            'TxnType' => 'SALS',
            'ResponseCode' => '1',
            'ReasonCode' => '1',
            'ReasonCodeDesc' => 'Transaction is successful',
            'MerID' => '9809687882',
            'AcqID' => '407387',
            'OrderID' => '000001991',
            'Signature' => 'TG+Zb6VJUcmRLqndsecvaAoodto=',
            'ReferenceNo' => '061800734279',
            'PaddedCardNo' => '526431******8303',
            'AuthCode' => '734279',
            'CavvEciInd' => 'N',
            'CAVV' => '',
            'ECI' => '0',
            'transStatus' => 'Y',
            'authMode' => 'E',
        );*/


        try {
            if (!empty($params)) {
                $params['transStatus'] = "C";

                $responseCode = $params['ResponseCode'];
                $reasonCode = $params['ReasonCode'];
                $reasonDescription = $params['ReasonCodeDesc'];

                if (!empty($responseCode) && $responseCode == 1) {
                    $params['transStatus'] = "Y";
                } elseif (!empty($responseCode) && $responseCode == 2) {
                    $params['transStatus'] = "C";
                } elseif (!empty($responseCode) && $responseCode == 3) {
                    $params['transStatus'] = "C";
                } else {
                    $params['transStatus'] = "C";
                }
                $params['authMode'] = 'E';
                $this->command->execute(['response' => $params]);
            }

        } catch (\Exception $e) {
            $this->logger->critical($e);
            /*print_r($e->getMessage());
              exit('fail');*/
            $processor->load(['response_failure']);
            return $resultLayout;
        }
        $this->dataPersistor->set('request_data', "");
        $this->dataPersistor->set('response_data', $params);

        switch ($params['transStatus']) {
            case self::$transStatusSuccess:
                $processor->load(['response_success']);
                break;
            case self::$transStatusCancel:
                $processor->load(['response_cancel']);
                break;
            case self::$transStatusFailure:
                $processor->load(['response_failure']);
                break;
            default:
                $processor->load(['response_failure']);
                break;
        }

        return $resultLayout;
    }
}
