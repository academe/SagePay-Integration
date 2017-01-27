<?php

namespace Academe\SagePay\Psr7\Request;

/**
 * The transaction value object to send a transaction to Sage Pay.
 * See https://test.sagepay.com/documentation/#transactions
 */

use UnexpectedValueException;
use Academe\SagePay\Psr7\Model\Endpoint;
use Academe\SagePay\Psr7\Model\Auth;
use Academe\SagePay\Psr7\PaymentMethod\PaymentMethodInterface;
use Academe\SagePay\Psr7\Money\AmountInterface;

class CreateDeferred extends CreatePayment
{
    protected $transactionType = AbstractRequest::TRANSACTION_TYPE_DEFERRED;
}
