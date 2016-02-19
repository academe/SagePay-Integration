<?php namespace Academe\SagePay\Psr7\Response;

/**
 * Value object holding the merchant session key returned by SagePay.
 * TODO: make expiry optional?
 */

use Exception;
use UnexpectedValueException;

use DateTime;
use DateTimeZone;

use Academe\SagePay\Psr7\Helper;
use Psr\Http\Message\ResponseInterface;

class SessionKey extends AbstractResponse
{
    protected $merchantSessionKey;
    protected $expiry;

    /**
     * $data array|object|ResponseInterface
     */
    public function __construct($data, $httpCode = null)
    {
        // If $data is a PSR-7 message, then extract what we need.
        if ($data instanceof ResponseInterface) {
            $this->setHttpCode($data->getStatusCode());

            if ($data->hasHeader('Content-Type') && $data->getHeaderLine('Content-Type') == 'application/json') {
                $data = json_decode($data->getBody());
            } else {
                $data = [];
            }
        } else {
            $this->setHttpCode($this->deriveHttpCode($httpCode, $data));
        }

        $this->merchantSessionKey = Helper::structureGet($data, 'merchantSessionKey');

        $expiry = Helper::structureGet($data, 'expiry');

        if (isset($expiry)) {
            $this->expiry = Helper::parseDateTime($expiry);
        }
    }

    /**
     * Create an instance of this object from an array or
     * value object. This would normally be the return body from SagePay.
     * Conversion from JSON needs to be done before this point.
     *
     * @deprecated
     */
    public static function fromData($data, $httpCode = null)
    {
        return new static($data, $httpCode);
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
     * Reduce the object to an array so it can be serialised.
     */
    public function toArray()
    {
        return [
            'merchantSessionKey' => $this->merchantSessionKey,
            'expiry' => $this->expiry->format(Helper::SAGEPAY_DATE_FORMAT),
        ];
    }

    /**
     * Return the authorisation HTTP headers for the session key.
     */
    public function getAuthHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->getMerchantSessionKey(),
        ];
    }
}
