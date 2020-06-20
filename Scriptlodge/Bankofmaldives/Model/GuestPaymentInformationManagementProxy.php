<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scriptlodge\Bankofmaldives\Model;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Scriptlodge\Bankofmaldives\Api\GuestPaymentInformationManagementProxyInterface;

class GuestPaymentInformationManagementProxy implements GuestPaymentInformationManagementProxyInterface
{
    /**
     * @var GuestPaymentInformationManagementInterface
     */
    private $guestPaymentInformationManagement;

    /**
     * GuestPaymentInformationManagementProxy constructor.
     * @param GuestPaymentInformationManagementInterface $guestPaymentInformationManagementInterface
     */
    public function __construct(
        GuestPaymentInformationManagementInterface $guestPaymentInformationManagementInterface
    )
    {
        $this->guestPaymentInformationManagement = $guestPaymentInformationManagementInterface;
    }

    /**
     * Proxy handler for guest place order
     *
     * @param string $cartId
     * @param string $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return int Order ID.
     * @throws CouldNotSaveException
     */
    public function savePaymentInformationAndPlaceOrder(
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    )
    {
        return $this->guestPaymentInformationManagement
            ->savePaymentInformationAndPlaceOrder(
                $cartId,
                $email,
                $paymentMethod,
                $billingAddress
            );
    }
}
