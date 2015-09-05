<?php namespace Academe\SagePayJs\Models;

/**
 * Value object holding the merchant session key returned by SagePay.
 */

use Exception;
use UnexpectedValueException;

use DateTime;
use DateTimeZone;

class SessionKey
{
    protected $merchantSessionKey;
    protected $expiry;

    public function __construct($merchantSessionKey, $expiry)
    {
        $this->merchantSessionKey = $merchantSessionKey;
        $this->setExpiry($expiry);
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

    // TODO: this is identical to in the CardDetail model.
    // The validation and type conversion should be separated out from the assignment
    // into a helper class or a trait.
    protected function setExpiry($expiry)
    {
        // The expiry can be supplied by SagePay as an ISO8601 string, though other
        // formats are accepted here.
        // It will be converted to a PHP DateTime if supplied as a string.

        try {
            if (is_string($expiry)) {
                // Supplied timestamp string should be ISO 8601 format.
                // Use a default UTC timezone for any relative dates that SagePay
                // may give us. Hopefully that won't be the case.

                $this->expiry = new DateTime($expiry, new DateTimeZone('UTC'));
            } elseif ($expiry instanceof DateTime) {
                $this->expiry = $expiry;
            } elseif (is_int($expiry)) {
                // Teat as a unix timestamp.
                $this->expiry = new DateTime();
                $this->expiry->setTimestamp($expiry);
            } else {
                throw new UnexpectedValueException('Unexpected expiry time type');
            }
        } catch(Exception $e) {
            throw new UnexpectedValueException('Unexpected expiry time format', $e->getCode(), $e);
        }
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
     * the form that submits to SagePay. The array contains all the
     * attributes needed to create the input element.
     */
    public function toAttributes()
    {
        return [
            'type' => 'hidden',
            'data-sagepay' => 'merchantSessionKey',
            'value' => $this->merchantSessionKey,
        ];
    }
}
