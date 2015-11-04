<?php namespace Academe\SagePayMsg\Message;

/**
 * Value object to hold the card identifier, returned by SagePay.
 * Reasonable validation is done at creation.
 */

use DateTime;
use DateTimeZone;

use Exception;
use UnexpectedValueException;

use Academe\SagePayMsg\Helper;

class CardIdentifierResponse extends AbstractResponse
{
    protected $cardIdentifier;
    protected $expiry;
    protected $cardType;

    public function __construct($cardIdentifier, $expiry, $cardType)
    {
        $this->cardIdentifier = $cardIdentifier;
        $this->expiry = Helper::parseDateTime($expiry);
        $this->cardType = $cardType;
    }

    public function getCardIdentifier()
    {
        return $this->cardIdentifier;
    }

    public function getExpiry()
    {
        return $this->expiry;
    }

    public function getCardType()
    {
        return $this->cardType;
    }

    public function isExpired()
    {
        // Use the default system timezone; the DateTime comparison
        // operation will handle any timezone conversions.
        // Note that this does not do a remote check with the Sage Pay
        // API. We can only find out if it is really still valid by
        // attempting to use it.

        $time_now = new DateTime();

        return ! isset($this->expiry) || $time_now > $this->expiry;
    }

    /**
     * Return an instantiation from the data returned by SagePay.
     */
    public static function fromData($data, $httpCode = null)
    {
        $cardIdentifier = Helper::structureGet($data, 'cardIdentifier', null);
        $expiry = Helper::structureGet($data, 'expiry', null);
        $cardType = Helper::structureGet($data, 'cardType', null);

        $response = new static($cardIdentifier, $expiry, $cardType);

        $response->storeHttpCode($response, $data, $httpCode);

        return $response;
    }

    /**
     * Reduce the object to an array so it can be serialised.
     */
    public function toArray()
    {
        return [
            'cardIdentifier' => $this->cardIdentifier,
            'expiry' => $this->expiry->format(Helper::SAGEPAY_DATE_FORMAT),
            'cardType' => $this->cardType,
        ];
    }
}
