<?php namespace Academe\SagePayJs\Message;

/**
 * Value object to hold the card details, for sending to SagePay.
 * The card details will normally only be given values in advance
 * during testing. In production this will be left empty and this
 * class just used as a helper for generating the card field on the
 * merchant site form.
 * But with the right PCI compliance, the details could be captured
 * by the merchant site and sent direct to SagePay server-to-server,
 * as SagePay Direct would.
 */

use Exception;
use UnexpectedValueException;

class CardIdentifierRequest
{
    protected $cardholderName;
    protected $cardNumber;
    protected $expiryDate;
    protected $securityCode;

    /**
     * TODO: validation
     * $expiryDate MMYY (maybe convert some common formats).
     * $cardNumber Lunn check.
     * $securityCode Digits only.
     */
    public function __construct($cardholderName = null, $cardNumber = null, $expiryDate = null, $securityCode = null)
    {
        $this->cardholderName = $cardholderName;
        $this->cardNumber = $cardNumber;
        $this->expiryDate = $expiryDate;
        $this->securityCode = $securityCode;
    }

    /**
     * An array of arrays, each containing the attributes required for the HTML
     * input elements in the payment form.
     */
    public function toAttributes()
    {
        return [
            ['type' => 'text', 'data-sagepay' => 'cardholderName', 'value' => $this->cardholderName],
            ['type' => 'text', 'data-sagepay' => 'cardNumber', 'value' => $this->cardNumber],
            ['type' => 'text', 'data-sagepay' => 'expiryDate', 'value' => $this->expiryDate],
            ['type' => 'text', 'data-sagepay' => 'securityCode', 'value' => $this->securityCode],
        ];
    }

    public function getCardholderName()
    {
        return $this->cardholderName;
    }

    public function getCardNumber()
    {
        return $this->cardNumber;
    }

    public function getExpiryDate)
    {
        return $this->expiryDate;
    }

    public function getSecurityCode()
    {
        return $this->securityCode;
    }
}
