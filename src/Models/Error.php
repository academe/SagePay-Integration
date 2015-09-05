<?php namespace Academe\SagePayJs\Models;

/**
 * Value object to hold an error, returned by SagePay when posting a transaction.
 * HTTP return code will be 422 to see one of these.
 */

use Exception;
use UnexpectedValueException;

class Error
{
    protected $code;
    protected $description;
    protected $property;

    public function __construct($code, $description, $property = null)
    {
        $this->code = $code;
        $this->description = $description;
        $this->property = $property;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getProperty()
    {
        return $this->property;
    }
}
