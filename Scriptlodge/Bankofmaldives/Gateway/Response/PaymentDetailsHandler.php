<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scriptlodge\Bankofmaldives\Gateway\Response;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class PaymentDetailsHandler implements HandlerInterface
{
    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $ReferenceNo="";
        if(isset($response['ReferenceNo'])){
            $ReferenceNo= $response['ReferenceNo'];
        }
        $payment->setTransactionId($ReferenceNo);
        $payment->setLastTransId($ReferenceNo);
        $payment->setAdditionalInformation('response', $response);
        $payment->setIsTransactionClosed(false);
    }
}
