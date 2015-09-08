<?php namespace Academe\SagePayMsg\Models;

/**
 * Value object used to define the shipping recipient and address.
 * Reasonable validation is done at creation.
 */

use Exception;
use UnexpectedValueException;

class ShippingDetails extends BillingDetails
{
    protected $firstName;
    protected $lastName;
    protected $address;

    // The prefix is added to the name fields when sending to SagePay.
    protected $nameFieldPrefix = 'recipient';

    // The prefix added to address name fields.
    protected $addressFieldPrefix = 'shipping';

    /**
     * Body fragment for the shipping details.
     * These are all on the same level, with field name prefixes.
     */
    public function getBody()
    {
        return array_merge(
            $this->address->getBody(),
            [
                $this->addNamePrefix('firstName') => $this->firstName,
                $this->addNamePrefix('lastName') => $this->lastName,
            ]
        );
    }
}
