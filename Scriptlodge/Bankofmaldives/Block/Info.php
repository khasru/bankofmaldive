<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scriptlodge\Bankofmaldives\Block;

use Magento\Framework\Phrase;


class Info extends \Magento\Payment\Block\ConfigurableInfo
{
    /**
     * Returns label
     *
     * @param string $field
     * @return Phrase
     */
    protected function getLabel($field)
    {
        return parent::getLabel($field);
    }

    /**
     * Returns value view
     *
     * @param string $field
     * @param string $value
     * @return string | Phrase
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getValueView($field, $value)
    {
        return parent::getValueView($field, $value);
    }

    /**
     * Prepare Bankofmaldives-specific payment information
     *
     * @param \Magento\Framework\DataObject|array|null $transport
     * @return \Magento\Framework\DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();
        $_additionalInfo = $payment->getAdditionalInformation();
        $info = array();
        if (isset($_additionalInfo['response'])) {
           // print_r($_additionalInfo['response']);

            /* This will equal to 1, 2 or 3. Basically 1 means that the transaction was authorized, 2 means it was
             declined and 3 means that an error has occurred .*/
            $ReferenceNo=isset($_additionalInfo['response']['ReferenceNo']) ? $_additionalInfo['response']['ReferenceNo'] : "";
            $ResponseCode=isset($_additionalInfo['response']['ResponseCode']) ? $_additionalInfo['response']['ResponseCode'] : "";
            $ReasonCode = isset($_additionalInfo['response']['ReasonCode']) ? $_additionalInfo['response']['ReasonCode'] : "";
            $Reason = isset($_additionalInfo['response']['ReasonCodeDesc']) ? $_additionalInfo['response']['ReasonCodeDesc'] : "";

            if ($ResponseCode == 1) {
                $info['Payment status'] = $Reason;
                $info['ReferenceNo'] = $ReferenceNo;

            } elseif ($ResponseCode == 2) {
                $info['Payment status'] = "Transaction was declined";
                $info['Reason']=$Reason;
            } elseif ($ResponseCode == 3) {
                $info['Payment status'] = "Error";
                $info['Reason']=$Reason;
            }

        }
        return $transport->addData($info);
    }
}
