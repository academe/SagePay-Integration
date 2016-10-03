<?php

namespace Academe\SagePay\Psr7\Response\Model;

/**
 * Card details response object.
 */

use Academe\SagePay\Psr7\Helper;
use JsonSerializable;

class Card implements JsonSerializable
{
    /**
     * @var Tokenised card.
     */
    protected $cardIdentifier;

    /**
     * @var Flag indicates this is a reusable card identifier; it has been used before.
     */
    protected $reusable;

    /**
     * @var Captured (safe) details for the card.
     * TODO: move these to response card class (this is requestcard class).
     */
    protected $cardType;
    protected $lastFourDigits;
    protected $expiryDate; // MMYY

    /**
     * Card constructor.
     *
     * @param string|null $cardType
     * @param string|null $lastFourDigits
     * @param string|null $expiryDate
     * @param string|null $cardIdentifier
     * @param boolean|null $reusable
     */
    public function __construct(
        $cardType = null,
        $lastFourDigits = null,
        $expiryDate = null,
        $cardIdentifier = null,
        $reusable = null
    ) {
        if (isset($cardType)) {
            $this->cardType = $cardType;
        }

        if (isset($lastFourDigits)) {
            $this->lastFourDigits = $lastFourDigits;
        }

        //  TODO: validate MMYY
        if (isset($expiryDate)) {
            $this->expiryDate = $expiryDate;
        }

        if (isset($cardIdentifier)) {
            $this->cardIdentifier = $cardIdentifier;
        }

        if (isset($reusable)) {
            $this->reusable = (bool)$reusable;
        }
    }

    /**
     * Construct an instance from stored data (e.g. JSON serialised object).
     */
    public static function fromData($data)
    {
        // For convenience.
        if (is_string($data)) {
            $data = json_decode($data);
        }

        // The data will normally be in a "card" wrapper element.
        // Remove it to make processing easier.
        if ($card = Helper::dataGet($data, 'card')) {
            $data = $card;
        }

        return new static(
            Helper::dataGet($data, 'cardType'),
            Helper::dataGet($data, 'lastFourDigits'),
            Helper::dataGet($data, 'expiryDate'),
            Helper::dataGet($data, 'cardIdentifier'),
            Helper::dataGet($data, 'reusable')
        );
    }

    /**
     * Serialisation for storage.
     * @return array
     */
    public function jsonSerialize()
    {
        $message = ['card' => []];

        if ($this->cardType !== null) {
            $message['card']['cardType'] = $this->cardType;
        }

        if ($this->lastFourDigits !== null) {
            $message['card']['lastFourDigits'] = $this->lastFourDigits;
        }

        if ($this->expiryDate !== null) {
            $message['card']['expiryDate'] = $this->expiryDate;
        }

        if ($this->cardIdentifier !== null) {
            $message['card']['cardIdentifier'] = $this->cardIdentifier;
        }

        if ($this->reusable !== null) {
            $message['card']['reusable'] = $this->reusable;
        }

        return $message;
    }

    /**
     * Getter for the type of credit card.
     * There is no definitive list of card types, but "Visa", "MasterCard" and
     * "American Express" are given as examples.
     * return string|null Null if no card type present or not a card
     */
    public function getCardType()
    {
        return $this->cardType;
    }

    /**
     * Getter for the last four digits of the credit card.
     * return string|null Null if no digits present or not a card
     */
    public function getLastFourDigits()
    {
        return $this->lastFourDigits;
    }

    /**
     * Getter for the raw expiry date of the credit card.
     * return string|null Format MMYY
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * return string|null Month number, format MM (leading zero)
     */
    public function getExpiryMonth()
    {
        $expiry = $this->getExpiryDate();

        if (! preg_match('/[0-9]{4}/', $expiry)) {
            return null;
        }

        return substr($expiry, 0, 2);
    }

    /**
     * No attempt is made to expand the year into four digits.
     * return string|null Year number, format YY
     */
    public function getExpiryYear()
    {
        $expiry = $this->getExpiryDate();

        if (! preg_match('/[0-9]{4}/', $expiry)) {
            return null;
        }

        return substr($expiry, 2, 2);
    }
}
