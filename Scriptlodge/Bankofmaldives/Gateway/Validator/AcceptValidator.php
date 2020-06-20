<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scriptlodge\Bankofmaldives\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Sales\Model\Order\Payment;

class AcceptValidator extends AbstractValidator
{
    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $paymentDO = SubjectReader::readPayment($validationSubject);
        $payment = $paymentDO->getPayment();
        $isValid = true;
        $fails = [];
        $statements = [];

        if ($statements) {
            foreach ($statements as $statementResult) {

                if (!$statementResult[0]) {
                    $isValid = false;
                    $fails[] = $statementResult[1];
                }
            }
        }

        return $this->createResult($isValid, $fails);
    }
}
