<?php namespace Academe\SagePayMsg\PaymentMethod;

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
