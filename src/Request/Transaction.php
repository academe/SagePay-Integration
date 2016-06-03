<?php

namespace Academe\SagePay\Psr7\Request;

/**
 * The transaction value object to send a transaction to Sage Pay.
 * See https://test.sagepay.com/documentation/#transactions
 *
 * @deprecated Use Payment instead.
 */

use UnexpectedValueException;
use Academe\SagePay\Psr7\Model\Endpoint;
use Academe\SagePay\Psr7\Model\Auth;
use Academe\SagePay\Psr7\PaymentMethod\PaymentMethodInterface;
use Academe\SagePay\Psr7\Money\AmountInterface;
use Academe\SagePay\Psr7\Model\AddressInterface;
use Academe\SagePay\Psr7\Model\PersonInterface;

class Transaction extends Payment
{
    public function __construct(
        Endpoint $endpoint,
        Auth $auth,
        $transactionType,
        PaymentMethodInterface $paymentMethod,
        $vendorTxCode,
        AmountInterface $amount,
        $description,
        AddressInterface $billingAddress,
        PersonInterface $customer,
        AddressInterface $shippingAddress = null,
        PersonInterface $shippingRecipient = null,
        array $options = []
    ) {
        parent::__construct(
            $endpoint,
            $auth,
            $paymentMethod,
            $vendorTxCode,
            $amount,
            $description,
            $billingAddress,
            $customer,
            $shippingAddress,
            $shippingRecipient,
            $options
        );
    }
}
