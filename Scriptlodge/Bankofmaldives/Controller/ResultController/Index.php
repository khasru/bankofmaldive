<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scriptlodge\Bankofmaldives\Controller\ResultController;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Scriptlodge\Bankofmaldives\Helper\Data;

/**
 * Redirects to checkout cart page with appropriate message
 *
 * Class Index
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Redirect types.
     *
     * @var string
     */
    private static $cancelRedirectType = 'cancel';

    /**
     * @var string
     */
    private static $failureRedirectType = 'failure';

    /**
     * @var string
     */
    private static $successRedirectType = 'success';

    /**
     * Relative urls for different redirect types.
     *
     * @var string
     */
    private static $defaultRedirectUrl = 'checkout/cart';

    /**
     * @var string
     */
    private static $successRedirectUrl = 'checkout/onepage/success';

    /**
     * Constructor
     *
     * @param Context $context
     * @param PlaceTransactionService $placeTransactionService
     * @param Session $checkoutSession
     */
    public function __construct(
        Context $context,
        Data $bmlHelper,
        DataPersistorInterface $dataPersistor

    )
    {
        $this->dataPersistor = $dataPersistor;
        $this->bmlHelper = $bmlHelper;
        parent::__construct($context);
    }


    /**
     * @inheritdoc
     */
    public function execute()
    {

        $params = $this->getRequest()->getParams();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $redirectUrl = self::$defaultRedirectUrl;
        if (!isset($params['type'])) {
            return $resultRedirect->setPath($redirectUrl);
        }

        $response_data = $this->dataPersistor->get('response_data');

        $cancelMsg = __('Your purchase process has been cancelled.');
        if (isset($response_data['ReasonCode'])) {
            $ReasonMsg = $this->bmlHelper->getErrorResone($response_data['ReasonCode']);
            if ($ReasonMsg) {
                $cancelMsg .= " Reason: " . $ReasonMsg;
            }
        }
        $this->dataPersistor->set('response_data', "");

        switch (trim($params['type'], '/')) {
            case self::$successRedirectType:
                $redirectUrl = self::$successRedirectUrl;
                break;
            case self::$cancelRedirectType:
                $this->messageManager->addErrorMessage($cancelMsg);
                break;
            case self::$failureRedirectType:
                $this->messageManager->addErrorMessage($cancelMsg);
                break;
            default:
                $this->messageManager
                    ->addErrorMessage(__('Something went wrong while processing your order. Please try again later.'));
        }
        return $resultRedirect->setPath($redirectUrl);
    }
}
