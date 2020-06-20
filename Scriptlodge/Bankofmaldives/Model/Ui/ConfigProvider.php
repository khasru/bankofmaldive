<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scriptlodge\Bankofmaldives\Model\Ui;


use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Checkout\Model\ConfigProviderInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const BML_CODE = 'bml';
    const REDIRECT_DATA_URL = 'bml/html/redirect';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Repository
     */
    protected $assetRepo;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Repository $assetRepo,
        RequestInterface $request,
        LoggerInterface $logger
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->assetRepo = $assetRepo;
        $this->request = $request;
        $this->logger = $logger;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::BML_CODE => [
                    'redirectDataUrl' => $this->urlBuilder->getUrl(self::REDIRECT_DATA_URL, ['_secure' => true]),
                    //'paymentAcceptanceMarkHref' => $this->urlBuilder->getUrl(),
                    'paymentAcceptanceMarkSrc' => $this->getPaymentImageUrl(),
                    'paymentTermsAndConditions' => $this->getTermsConditionUrl()
                ]
            ]
        ];
    }


    /**
     * Retrieve CVV tooltip image url
     *
     * @return string
     */
    public function getPaymentImageUrl()
    {
        return $this->getViewFileUrl('Scriptlodge_Bankofmaldives::bml.png');
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    public function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->request->isSecure()], $params);
            return $this->assetRepo->getUrlWithParams($fileId, $params);
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            return $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']);
        }
    }


    /**
     * Retrieve url of a terms and condition file
     * @param array $params
     * @return string
     */
    public function getTermsConditionUrl(array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->request->isSecure()], $params);
            return $this->urlBuilder->getUrl('terms-and-conditions', $params);

        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            return $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']);
        }
    }

}