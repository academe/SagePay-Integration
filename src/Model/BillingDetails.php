<?php namespace Academe\SagePayMsg\Model;

/**
 * Value object used to define the shipping recipient and address.
 * Reasonable validation is done at creation.
 */

use Exception;
use UnexpectedValueException;

class BillingDetails
{
    /**
     * @var Person
     */
    protected $person;
    protected $address;

    /**
     * @var string The prefix added to the name fields when sending to SagePay
     */
    protected $nameFieldPrefix = 'customer';

    /**
     * @var string The prefix added to address name fields
     */
    protected $addressFieldPrefix = '';

    /**
     * @param Person $person Details of the person that will be billed
     * @param Address $address The billing address of the person that will be billed
     */
    public function __construct(Person $person, Address $address)
    {
        $this->person = $person->withFieldPrefix($this->nameFieldPrefix);

        // This prefix is added to the address fields when sending to SagePay.
        $this->address = $address->withFieldPrefix($this->addressFieldPrefix);
    }

    /**
     * These are all on two levels, with field name prefixes.
     *
     * @return array Body fragment for the billing details, requiring conversion to JSON for the API
     */
    public function getBody()
    {
        return array_merge(
            ['billingAddress' => $this->address->getBody()],
            $this->person->getBody()
        );
    }

    /**
     * Add the "name" prefix to a field name, capitalising the original
     * initial letter of the field if necessary to keep camel-case.
     */
    /**
     * @param $field The field name without a prefix
     *
     * @return string The field name with the current prefix added, with camel capitalisation
     */
    protected function addNamePrefix($field)
    {
        if ( ! $this->nameFieldPrefix) {
            return $field;
        }

        return $this->nameFieldPrefix . ucfirst($field);
    }

    /**
     * @return Address The billing address object
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return array The billing address as an array for constructing a message body fragment
     */
    public function getAddressBody()
    {
        return $this->address->getBody();
    }

    /**
     * @return string the first name of the customer
     */
    public function getFirstName()
    {
        return $this->person->getFirstName();
    }

    /**
     * @return string The last name of the customer
     */
    public function getLastName()
    {
        return $this->getLastName();
    }
}
