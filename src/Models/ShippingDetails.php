<?php namespace Academe\SagePayJs\Models;

/**
 * Value object used to define the shipping recipient and address.
 * Reasonable validation is done at creation.
 */

use Exception;
use UnexpectedValueException;

class ShippingDetails
{
    protected $firstName;
    protected $lastName;
    protected $address;

    // The prefix is added to the name fields when sending to SagePay.
    protected $nameFieldPrefix = 'recipient';

    // TODO: should the firstName and lastName be a Person class, with its own
    // validation built-in?
    public function __construct($firstName, $lastName, Address $address)
    {
        // These fields are always mandatory.
        foreach(array('firstName', 'lastName') as $field_name) {
            if (empty($$field_name)) {
                throw new UnexpectedValueException(sprintf('Field "%s" is mandatory but not set.', $field_name));
            }
        }

        $this->firstName = $firstName;
        $this->lastName = $lastName;

        // This prefix is added to the address fields when sending to SagePay.
        $this->address = $address->withFieldPrefix('shipping');
    }

    public function toArray()
    {
        return array_merge(
            array(
                $this->addNamePrefix('firstName') => $this->firstName,
                $this->addNamePrefix('lastName') => $this->lastName,
            ),
            $this->address->toArray()
        );
    }

    protected function addNamePrefix($field)
    {
        if ( ! $this->nameFieldPrefix) {
            return $field;
        }

        return $this->nameFieldPrefix . ucfirst($field);
    }
}
