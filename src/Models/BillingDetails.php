<?php namespace Academe\SagePayMsg\Models;

/**
 * Value object used to define the shipping recipient and address.
 * Reasonable validation is done at creation.
 */

use Exception;
use UnexpectedValueException;

class BillingDetails
{
    protected $person;
    protected $address;

    // The prefix is added to the name fields when sending to SagePay.
    protected $nameFieldPrefix = 'customer';

    // The prefix added to address name fields.
    protected $addressFieldPrefix = '';

    // TODO: should the firstName and lastName be a Person class, with its own
    // validation built-in?
    public function __construct(Person $person, Address $address)
    {
        $this->person = $person->withFieldPrefix($this->nameFieldPrefix);

        // This prefix is added to the address fields when sending to SagePay.
        $this->address = $address->withFieldPrefix($this->addressFieldPrefix);
    }

    /**
     * Body fragment for the billing details.
     * These are all on two levels, with field name prefixes.
     */
    public function getBody()
    {
        return array_merge(
            ['billingAddress' => $this->address->getBody()],
            $this->person->getBody()
        );
    }

    /**
     * Add the "name" profix to a field name, capitalising the original
     * initial letter of the field if necessary to keep cammel-case.
     */
    protected function addNamePrefix($field)
    {
        if ( ! $this->nameFieldPrefix) {
            return $field;
        }

        return $this->nameFieldPrefix . ucfirst($field);
    }

    /**
     * Return the address object.
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Return the address as an array for constructing a message body fragment.
     */
    public function getAddressBody()
    {
        return $this->address->getBody();
    }

    /**
     * Return the first name of the customer.
     */
    public function getFirstName()
    {
        return $this->person->getFirstName();
    }

    /**
     * Return the last name of the customer.
     */
    public function getLastName()
    {
        return $this->getLastName();
    }
}
