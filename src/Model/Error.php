<?php namespace Academe\SagePayMsg\Model;

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
    /**
     * @var
     */
    protected $code;
    protected $description;
    protected $property;

    /**
     * @param string|int $code The error code supplied by the remote API
     * @param string $description The textual detail of the error
     * @param null|string $property The property name (field name) of the property the error applies to
     */
    public function __construct($code, $description, $property = null)
    {
        $this->code = $code;
        $this->description = $description;
        $this->property = $property;
    }

    /**
     * @return int|string The error code supplied by the remote API
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string The textual detail of the error
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return null|string The property name (field name) of the property the error applies to
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * The statusCode and statusDetail is a legacy error format that seems to have crept
     * into some validation errors. If this is a long-term "feature" of the API, then
     * it may be worth translating some of the errors. For example statusCode 3123
     * is "The DeliveryAddress1 value is too long". This translates to code 1004 (Invalid length)
     * for the property "shippingDetails.shippingAddress1". Ideally we should not have
     * to do that.
     */

    /**
     * @param array|object $data Error data from the API to initialise the Error object
     *
     * @return static New instance of Error object
     */
    public static function fromData($data)
    {
        if ($data instanceof Error) {
            return $data;
        }

        $code = Helper::structureGet($data, 'code', Helper::structureGet($data, 'statusCode', null));
        $description = Helper::structureGet($data, 'description', Helper::structureGet($data, 'statusDetail', null));
        $property = Helper::structureGet($data, 'property', null);

        return new static($code, $description, $property);
    }
}
