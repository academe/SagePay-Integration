<?php

namespace Academe\SagePay\Psr7\Request\Model;

/**
 * Interface for a payment method request.
 */

interface PaymentMethodInterface extends \JsonSerializable
{
    /**
     * Returns the data that needs to be serialized when making a payment.
     *
     * @return array
     */
    public function payData();
}
