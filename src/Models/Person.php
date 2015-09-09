<?php namespace Academe\SagePayMsg\Models;

/**
 * Value object used to hold details about a person.
 * Details include just a first name and last name.
 */

use Exception;
use UnexpectedValueException;

class Person
{
    protected $firstName;
    protected $lastName;
    protected $email;
    protected $phone;

    protected $fieldPrefix = '';

    public function __construct($firstName, $lastName, $email = null, $phone = null)
    {
        // These fields are always mandatory.
        foreach(array('firstName', 'lastName') as $field_name) {
            if (empty($$field_name)) {
                throw new UnexpectedValueException(sprintf('Empty field "%s" is mandatory.', $field_name));
            }
        }

        $this->firstName = $firstName;
        $this->lastName = $lastName;

        $this->email = $email;
        $this->phone = $phone;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPhone()
    {
        return $this->lastPhone;
    }

    public function getBody()
    {
        // First/last name is always required.
        $return = $this->getNamesBody();

        // Email and phone is optional.
        if (isset($this->email)) {
            $return[$this->addFieldPrefix('email')] = $this->email;
        }

        if (isset($this->phone)) {
            $return[$this->addFieldPrefix('phone')] = $this->phone;
        }

        return $return;
    }

    public function getNamesBody()
    {
        // Name is mandatory.
        return [
            $this->addFieldPrefix('firstName') => $this->firstName,
            $this->addFieldPrefix('lastName') => $this->lastName,
        ];
    }

    protected function addFieldPrefix($field)
    {
        if ( ! $this->fieldPrefix) {
            return $field;
        }

        return $this->fieldPrefix . ucfirst($field);
    }

    /**
     * Set the field prefix used when returning the object as an array.
     */
    public function withFieldPrefix($fieldPrefix)
    {
        $copy = clone $this;
        $copy->fieldPrefix = $fieldPrefix;
        return $copy;
    }
}
