<?php

namespace Scriptlodge\Bankofmaldives\Model;

use Psr\Log\LoggerInterface;
use Scriptlodge\Bankofmaldives\Api\IpnInterface;
use Scriptlodge\Bankofmaldives\Gateway\Command\ResponseCommand;
use Scriptlodge\Bankofmaldives\Model\BankofmaldivesFactory;

class IpnManagement implements IpnInterface
{

    /**
     * @var ResponseCommand
     */
    private $command;
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
    /**
     * @var string
     */
    private static $transStatusFailure = 'F';

    /**
     * @var bankofmaldivesFactory
     */
    protected $bankofmaldivesFactory;

    /**
     * IpnManagement constructor.
     * @param ResponseCommand $command
     * @param LoggerInterface $logger
     * @param BankofmaldivesFactory $bankofmaldivesFactory
     */
    public function __construct(
        ResponseCommand $command,
        LoggerInterface $logger,
        BankofmaldivesFactory $bankofmaldivesFactory
    )
    {
        $this->command = $command;
        $this->bankofmaldivesFactory = $bankofmaldivesFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function savePaymentInformation($status, $invoice, $amount)
    {
        $params = [];
        try {
            if (!empty($status)) {
                $params['status'] = $status;
            }
            if (!empty($invoice)) {
                $params['invoice'] = $invoice;
            }
            if (!empty($amount)) {
                $params['amount'] = $amount;
            }

            if (empty($params)) {
                $params['transStatus'] = "C";
            } elseif ($status == 'ACCEPTED') {
                $params['transStatus'] = "Y";
            } elseif ($status == 'REJECTED') {
                $params['transStatus'] = "C";
            } else {
                $params['transStatus'] = "F";
            }
            $params['authMode'] = 'E';
            $params['ipn_request'] = true;

            $this->command->execute(['response' => $params]);
            return true;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            print_r($e->getMessage());
            //   exit('fail');

            return false;
        }
    }
}
