<?php namespace Academe\SagePayMsg\Models;

/**
 * Value object to hold an error, returned by SagePay when posting a transaction.
 * HTTP return code will be 422 to see one of these.
 * Other ~400 return codes will return just one error in the body, without a property.
 */

use Exception;
use UnexpectedValueException;

// FIXME: we are only extending AbstractMessage to get at the helper methods.
use Academe\SagePayMsg\Message\AbstractMessage;

class Error extends AbstractMessage
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
        $code = static::structureGet($data, 'code');
        $description = static::structureGet($data, 'description');
        $property = static::structureGet($data, 'property', null);

        return new static($code, $description, $property);
    }
}
