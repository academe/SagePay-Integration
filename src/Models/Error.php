<?php namespace Academe\SagePayMsg\Models;

/**
 * Value object to hold an error, returned by SagePay when posting a transaction.
 * Multiple validation errors will be returned when the HTTP return code is 422.
 * These will be held by the ErrorCollection class. Examples of 422 code errors are:
 *  1003    Missing mandatory field
 *  1004    Invalid length
 *  1005    Contains invalid characters
 *  1007    The card number has failed our validity checks and is invalid
 *  1008    The card is not supported
 *  1009    Contains invalid value
 * The 1XXX numbers are the SagePay erro codes. These will each include a property
 * name as they are targetted at specific fields that fail validation.
 *
 * Other ~400 return codes will return just one error in the body, without a property
 * as they are not targetted as specific fields.
 */

use Exception;
use UnexpectedValueException;

use Academe\SagePayMsg\Helper;

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

    public static function fromData($data)
    {
        $code = Helper::structureGet($data, 'code');
        $description = Helper::structureGet($data, 'description');
        $property = Helper::structureGet($data, 'property', null);

        return new static($code, $description, $property);
    }
}
