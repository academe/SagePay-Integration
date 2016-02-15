<?php namespace Academe\SagePay\Psr7\PaymentMethod;

/**
 * Interface for a payment method.
 */

interface PaymentMethodInterface
{
    /**
     * Return the body partial for message construction.
     */
    public function getBody();
}
