<?php namespace Academe\SagePayMsg\Message;

/**
 * Value object holding the merchant session key returned by SagePay.
 * TODO: make expiry optional?
 */

use Exception;
use UnexpectedValueException;

use DateTime;
use DateTimeZone;

use Academe\SagePayMsg\Helper;

class SessionKeyResponse extends AbstractResponse
{
    protected $merchantSessionKey;
    protected $expiry;

    public function __construct($merchantSessionKey, $expiry = null)
    {
        $this->merchantSessionKey = $merchantSessionKey;

        if (isset($expiry)) {
            $this->expiry = Helper::parseDateTime($expiry);
        }
    }

    /**
     * @return null|string
     */
    public function getMerchantSessionKey()
    {
        return $this->merchantSessionKey;
    }

    /**
     * @return null|DateTime The time at which the session key will expire
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    public function isExpired()
    {
        // Use the default system timezone; the DateTime comparison
        // operation will handle any timezone conversions.
        // A null expiry is considered to be expired.

        $time_now = new DateTime();

        return ! isset($this->expiry) || $time_now > $this->expiry;
    }

    /**
     * @returns bool True if the session key appears to be valid and usable.
     */
    public function isValid()
    {
        // Check if it has expired according to the time we have.
        if ($this->isExpired()) {
            return false;
        }

        // Do we have a 404 HTTP respons code recorded?
        if ($this->getHttpCode() !== null && $this->getHttpCode() === $this::NOT_FOUND) {
            return false;
        }

        // Is there even a session key set?
        if ($this->getMerchantSessionKey() === null) {
            return false;
        }

        // It has got through all the failure tests, so must be valid.
        // That doesn't mean it won't expire before it is used, or has not
        // been used the maximum number of times it can, but locally it looks
        // fine.

        return true;
    }

    /**
     * Return an array to support the generation of the hidden field in
     * the form that submits to Sage Pay (via sagepay.js). The array contains all the
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
    public static function fromData($data, $httpCode = null)
    {
        $merchantSessionKey = Helper::structureGet($data, 'merchantSessionKey');
        $expiry = Helper::structureGet($data, 'expiry');

        $response = new static($merchantSessionKey, $expiry);

        $response->storeHttpCode($response, $data, $httpCode);

        return $response;
    }

    /**
     * Reduce the object to an array so it can be serialised.
     */
    public function toArray()
    {
        return [
            'merchantSessionKey' => $this->merchantSessionKey,
            'expiry' => $this->expiry->format(Helper::SAGEPAY_DATE_FORMAT),
        ];
    }
}
