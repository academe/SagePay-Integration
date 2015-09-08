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

    protected $fieldPrefix = '';

    public function __construct($firstName, $lastName)
    {
        // These fields are always mandatory.
        foreach(array('firstName', 'lastName') as $field_name) {
            if (empty($$field_name)) {
                throw new UnexpectedValueException(sprintf('Field "%s" is mandatory but not set.', $field_name));
            }
        }

        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getBody()
    {
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
