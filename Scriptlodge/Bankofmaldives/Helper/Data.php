<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scriptlodge\Bankofmaldives\Helper;


use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Scriptlodge\Bankofmaldives\Model\BankofmaldivesFactory;
use Magento\Framework\Encryption\EncryptorInterface;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var array
     */
    private $postData = null;

    protected $bankofmaldivesFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerViewHelper $customerViewHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerViewHelper $customerViewHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptorInterface,
        BankofmaldivesFactory $bankofmaldivesFactory

    )
    {
        $this->_customerSession = $customerSession;
        $this->_customerViewHelper = $customerViewHelper;
        $this->bankofmaldivesFactory = $bankofmaldivesFactory;
        $this->scopeConfig = $scopeConfig;
        $this->encryptorInterface=$encryptorInterface;
        parent::__construct($context);
    }


    /**
     * Get user name
     *
     * @return string
     */
    public function getUserName()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return '';
        }
        /**
         * @var \Magento\Customer\Api\Data\CustomerInterface $customer
         */
        $customer = $this->_customerSession->getCustomerDataObject();

        return trim($this->_customerViewHelper->getCustomerName($customer));
    }

    /**
     * Get user email
     *
     * @return string
     */
    public function getUserEmail()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return '';
        }
        /**
         * @var CustomerInterface $customer
         */
        $customer = $this->_customerSession->getCustomerDataObject();

        return $customer->getEmail();
    }

    public function getConfigValue($path, $storeScope, $decrypt = false)
    {

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($decrypt) {
            return $this->encryptorInterface->decrypt($this->scopeConfig->getValue('payment/bml/' . $path, $storeScope));
        } else {
            return $this->scopeConfig->getValue('payment/bml/' . $path, $storeScope);
        }
    }


    /**
     * Get value  by key
     *
     * @return string
     */
    public function getResponseValue()
    {
        if (null === $this->postData) {
            $this->postData = (array)$this->getDataPersistor()->get('request_data');
            //  $this->getDataPersistor()->clear('request_data');
        }
        if ($this->postData) {
            return $this->postData;
        }
        return '';
    }

    public function clearResponseValue()
    {
        $this->getDataPersistor()->clear('request_data');
    }

    /**
     * Get Data Persistor
     *
     * @return DataPersistorInterface
     */
    private function getDataPersistor()
    {
        if ($this->dataPersistor === null) {
            $this->dataPersistor = ObjectManager::getInstance()
                ->get(DataPersistorInterface::class);
        }

        return $this->dataPersistor;
    }


    public function getErrorResone($code)
    {

        $_errorCode = [
            '000' => __('Transaction is successful'),
            '101' => __('Invalid field passed to 3D Secure MPI'),
            '109' => __('ACS Not Available'),
            '201' => __('Invalid ACS response format. Transaction is aborted.'),
            '202' => __('Cardholder failed the 3D authentication, password entered by cardholder is incorrect and transaction is aborted'),
            '203' => __('3D PaRes has invalid signature. Transaction is aborted'),
            '205' => __('Issuer ACS is unavailable to authenticate'),
            '300' => __('Transaction not approved'),
            '301' => __('Record not found'),
            '302' => __('Transation Not Allowed'),
            '303' => __('Invalid Merchant ID'),
            '304' => __('Transaction blocked by error 901'),
            '308' => __('Transaction is aborted. The Transaction was cancelled by the user.'),
            '900' => __('3D Transaction Timeout'),
            '903' => __('Comm. Timeout'),
            '901' => __('System Error'),
            '902' => __('Time out')
        ];

        if (array_key_exists($code, $_errorCode)) {
            return $_errorCode[$code];
        } else {
            return null;
        }

    }

}
