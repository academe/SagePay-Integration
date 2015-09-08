<?php namespace Academe\SagePay\Models;

/**
 * Value object used to define the shipping recipient and address.
 * Reasonable validation is done at creation.
 */

use Exception;
use UnexpectedValueException;

class BillingDetails
{
    protected $firstName;
    protected $lastName;
    protected $address;

    // The prefix is added to the name fields when sending to SagePay.
    protected $nameFieldPrefix = 'customer';

    // The prefix added to address name fields.
    protected $addressFieldPrefix = '';

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
        $this->address = $address->withFieldPrefix($this->addressFieldPrefix);
    }

    /*public function toArray()
    {
        return array_merge(
            array(
                $this->addNamePrefix('firstName') => $this->firstName,
                $this->addNamePrefix('lastName') => $this->lastName,
            ),
            $this->address->toArray()
        );
    }*/

    /**
     * Body fragment for the billing details.
     * These are all on two levels, with field name prefixes.
     */
    public function getBody()
    {
        return array_merge(
            [
                'billingAddress' => $this->address->getBody(),
            ],
            [
                $this->addNamePrefix('firstName') => $this->firstName,
                $this->addNamePrefix('lastName') => $this->lastName,
            ]
        );
    }

    protected function addNamePrefix($field)
    {
        if ( ! $this->nameFieldPrefix) {
            return $field;
        }

        return $this->nameFieldPrefix . ucfirst($field);
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getAddressBody()
    {
        return $this->address->getBody();
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }
}
