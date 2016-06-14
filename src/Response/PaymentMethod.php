<?php namespace Academe\SagePay\Psr7\Response;

/**
 * The payment method used in a transaction.
 * This is a generic value object for all payment type responses.
 * Unlike the requests that implement each payment type as a separate
 * class, the payment type response is just one class. The idea is to
 * provide consistency in the interface. If separate classes for each
 * payment type response proves to be useful in the future, then it
 * can be added, but the interface to this class will remain the same.
 */

use Academe\SagePay\Psr7\Helper;
use Psr\Http\Message\ResponseInterface;

class PaymentMethod extends AbstractResponse
{
    /**
     * The payment method type.
     * Supports: just "card" at this time.
     */
    protected $type;

    /**
     * Properties for payment method = 'card'
     */
    protected $cardType;
    protected $lastFourDigits;
    protected $expiryDate;

    /**
     * Collect values from the supplied data.
     *
     * @param $data
     * @return $this
     */
    protected function setData($data)
    {
        if (Helper::dataGet($data, 'paymentMethod.card', null)) {
            $this->type = 'card';

            $this->cardType = Helper::dataGet($data, 'paymentMethod.card.cardType', null);
            $this->lastFourDigits = Helper::dataGet($data, 'paymentMethod.card.lastFourDigits', null);
            $this->expiryDate = Helper::dataGet($data, 'paymentMethod.card.expiryDate', null);
        }

        return $this;
    }

    /**
     * Getter for the payment type.
     * return string Lower-case type name ("card")
     */
    public function getPaymentType()
    {
        return $this->type;
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

    /**
     * @inheritdoc
     */
    public static function isResponse($data)
    {
        return !empty(Helper::dataGet($data, 'paymentMethod'));
    }

    /**
     * Convenient serialisation for logging and debugging.
     * @return array
     */
    public function jsonSerialize()
    {
        $return = parent::jsonSerialize();

        $return['paymentType'] = $this->getPaymentType();

        if ($this->getPaymentType() === 'card') {
            $return['cardType'] = $this->getCardType();
            $return['lastFourDigits'] = $this->getLastFourDigits();
            $return['expiryDate'] = $this->getExpiryDate();
        }

        return $return;
    }
}
