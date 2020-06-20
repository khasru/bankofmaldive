<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scriptlodge\Bankofmaldives\Gateway\Request\HtmlRedirect;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Scriptlodge\Bankofmaldives\Helper\Data;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class OrderDataBuilder
 */
class OrderDataBuilder implements BuilderInterface
{

    private $live_gateWayUrl = "https://egateway.bankofmaldives.com.mv/bmlmpiprod/threed/MPI";

    private $sandbox_gateWayUrl = "https://ebanking.bankofmaldives.com.mv/bmlmpiuat/threed/MPI";

    /**
     * Response url
     */
    const RESPONSE_URL = 'bml/html/response';

    const CURRENCY = '462';


    const MerchantID = "MerID";
    const AcquirerID = "AcqID";
    const Password = "Password";
    const OrderID = "OrderID";
    const PurchaseAmt = "PurchaseAmt";
    const Instructions = "Instructions";
    const SignatureMethod = "SignatureMethod";
    const PurchaseCurrency = "PurchaseCurrency";
    const MerRespURL = "MerRespURL";
    const PurchaseCurrencyExponent = 'PurchaseCurrencyExponent';
    const Signature = "Signature";
    const TransactionUrl = "transaction_url";


    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var UrlInterface
     */
    private $urlHelper;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;
    /*
      ZeroGravity\PortWallet\Helper\Data;
    */
    protected $helperData;

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     * @param UrlInterface $urlHelper
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        ConfigInterface $config,
        EncryptorInterface $encryptorInterface,
        UrlInterface $urlHelper,
        Data $helperData,
        ResolverInterface $localeResolver
    )
    {
        $this->config = $config;
        $this->encryptorInterface = $encryptorInterface;
        $this->urlHelper = $urlHelper;
        $this->localeResolver = $localeResolver;
        $this->helperData = $helperData;
    }


    private function getConfigaration($storeId)
    {
        $config = array();

        $merchant_id = $this->encryptorInterface->decrypt($this->config->getValue('merchant_id', $storeId));
        $acquirer_id = $this->encryptorInterface->decrypt($this->config->getValue('acquirer_id', $storeId));
        $password = $this->encryptorInterface->decrypt($this->config->getValue('password', $storeId));
        $instructions = $this->config->getValue('instructions', $storeId);

        $debug = $this->config->getValue('debug', $storeId);
        $test = $this->config->getValue('sandbox_flag', $storeId);

        if ($merchant_id && $acquirer_id) {
            $config['merchant_id'] = $merchant_id;
            $config['acquirer_id'] = $acquirer_id;
            $config['password'] = $password;
            $config['instructions'] = $instructions;
            $config['debug'] = $debug;
            $config['test'] = $test;
        }

        return $config;
    }

    public function getPurchaseAmt($orderAmount)
    {

        //  $orderAmount=$this->getOrder()->getGrandTotal();
        $roundAmount = $orderAmount * 100;

        $noOfDigit = 12;
        $length = strlen((string)$roundAmount);
        for ($i = $length; $i < $noOfDigit; $i++) {
            $roundAmount = '0' . $roundAmount;
        }

        return $roundAmount;
    }

    public function getSignatureMethod()
    {
        return 'SHA1';
    }

    /*The fields must be set in the following order:
    (Password + Merchant ID + Acquirer ID + Order ID + Purchase Amount + Purchase Currency)
    */
    public function getSignature($config)
    {

        $password = $config['password'];
        $merID = $config['merchant_id'];
        $acqID = $config['acquirer_id'];
        $orderId = $config['order_id'];
        $purchaseAmt =$this->getPurchaseAmt($config['order_amount']);
        $currencyCode = self::CURRENCY;

        $signString = $password . $merID . $acqID . $orderId . $purchaseAmt . $currencyCode;
        $signature = base64_encode(sha1($signString, true));

        return $signature;
    }

    public function getGetwayReturnUrl()
    {
        return $this->urlHelper->getUrl(self::RESPONSE_URL);
    }


    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        $storeId = $order->getStoreId();
        $payment = $paymentDO->getPayment();

        $config = $this->getConfigaration($storeId);


        $orderId = $order->getId();

        $orderIncrementedId = $order->getOrderIncrementId();

        $orderAmount = sprintf('%.2F', $order->getGrandTotalAmount());

        $config['order_id'] = $orderIncrementedId;
        $config['order_amount'] = $orderAmount;

        $purchaseAmt = $this->getPurchaseAmt($orderAmount);

        $orderData = array(
            self::MerchantID => $config['merchant_id'],
            self::AcquirerID => $config['acquirer_id'],
            self::Password => $config['password'],
            self::Instructions => $config['instructions'],
            self::OrderID => $orderIncrementedId,
            self::PurchaseAmt => $purchaseAmt,
            self::SignatureMethod => $this->getSignatureMethod(),
            self::PurchaseCurrency => self::CURRENCY,
            self::PurchaseCurrencyExponent => '2',
            self::MerRespURL => $this->getGetwayReturnUrl(),
            self::Signature => $this->getSignature($config),
            self::TransactionUrl => $this->getTransactionUrl($config)
        );

        return $orderData;

    }

    public function getTransactionUrl($config)
    {
        if ($config['test']) {
            return $this->sandbox_gateWayUrl;
        } else {
            return $this->live_gateWayUrl;
        }

    }

}
