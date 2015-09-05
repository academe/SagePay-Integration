<?php namespace Academe\SagePayJs\Message;

/**
 * Value object to hold the card identifier, returned by SagePay.
 * Reasonable validation is done at creation.
 */

use DateTime;
use DateTimeZone;

use Exception;
use UnexpectedValueException;

class CardIdentifierResponse
{
    protected $cardIdentifier;
    protected $expiry;
    protected $cardType;

    // TODO: support initialisation using an array.
    // TODO: support population from JSON
    public function __construct($cardIdentifier, $expiry = null, $cardType = null)
    {
        if (is_array($cardIdentifier)) {
            if (array_key_exists('cardIdentifier', $cardIdentifier)) {
                $this->setCardIdentifier($cardIdentifier['cardIdentifier']);
            }

            if (array_key_exists('expiry', $cardIdentifier)) {
                $this->setExpiry($cardIdentifier['expiry']);
            }

            if (array_key_exists('cardType', $cardIdentifier)) {
                $this->setCardType($cardIdentifier['cardType']);
            }

            if (empty($this->cardIdentifier)) {
                throw new UnexpectedValueException('cardIdentifier element is not set');
            }
        } else {
            $this->setCardIdentifier($cardIdentifier);
            $this->setExpiry($expiry);
            $this->setCardType($cardType);
        }
    }

    protected function setCardIdentifier($cardIdentifier)
    {
        $this->cardIdentifier = $cardIdentifier;
    }

    public function getCardIdentifier()
    {
        return $this->cardIdentifier;
    }

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

    public function getExpiry()
    {
        return $this->expiry;
    }

    protected function setCardType($cardType)
    {
        $this->cardType = $cardType;
    }

    public function getCardType()
    {
        return $this->cardType;
    }

    public function isExpired()
    {
        // Use the default system timezone; the DateTime comparison
        // operation will handle any timezone conversions.

        $time_now = new DateTime();

        return ! isset($this->expiry) || $time_now > $this->expiry;
    }
}
