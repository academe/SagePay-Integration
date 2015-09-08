<?php namespace Academe\SagePayMsg\Message;

/**
 * Value object holding the merchant session key returned by SagePay.
 * TODO: make expiry optional?
 */

use Exception;
use UnexpectedValueException;

use DateTime;
use DateTimeZone;

class SessionKeyResponse extends AbstractMessage
{
    protected $merchantSessionKey;
    protected $expiry;

    public function __construct($merchantSessionKey, $expiry)
    {
        $this->merchantSessionKey = $merchantSessionKey;
        $this->expiry = $this->parseDateTime($expiry);
    }

    public function getMerchantSessionKey()
    {
        return $this->merchantSessionKey;
    }

    /**
     * This will return a DateTime class.
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    public function isExpired()
    {
        // Use the default system timezone; the DateTime comparison
        // operation will handle any timezone conversions.

        $time_now = new DateTime();

        return ! isset($this->expiry) || $time_now > $this->expiry;
    }

    /**
     * Return an array to support the generation of the hidden field in
     * the form that submits to SagePay (via sagepay.js). The array contains all the
     * attributes needed to create the input element.
     * TODO: make this an object that can handle its rendering too.
     */
    public function toHtmlElements()
    {
        return [
            'merchantSessionKey' => [
                'name' => 'input',
                'attributes' => [
                    'type' => 'hidden',
                    'data-sagepay' => 'merchantSessionKey',
                    'value' => $this->merchantSessionKey,
                ],
            ],
        ];
    }

    /**
     * Create an instance of this object from an array or
     * value object. This would normally be the return body from SagePay.
     * Conversion from JSON needs to be done before this point.
     */
    public static function fromData($data)
    {
        $merchantSessionKey = static::structureGet($data, 'merchantSessionKey');
        $expiry = static::structureGet($data, 'expiry');

        return new static($merchantSessionKey, $expiry);
    }

    /**
     * Reduce the object to an array so it can be serialised.
     */
    public function toArray()
    {
        return [
            'merchantSessionKey' => $this->merchantSessionKey,
            'expiry' => $this->expiry->format(static::SAGEPAY_DATE_FORMAT),
        ];
    }
}
