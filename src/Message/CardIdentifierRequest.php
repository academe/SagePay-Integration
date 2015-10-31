<?php namespace Academe\SagePayMsg\Message;

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

use Academe\SagePayMsg\Model\Auth;
use Academe\SagePayMsg\Message\SessionKeyResponse;

class CardIdentifierRequest extends AbstractRequest
{
    protected $resource_path = ['card-identifiers'];

    protected $auth;
    protected $sessionKeyResponse;

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
    public function __construct(Auth $auth, SessionKeyResponse $sessionKeyResponse, $cardholderName, $cardNumber, $expiryDate, $securityCode)
    {
        $this->auth = $auth;
        $this->sessionKeyResponse = $sessionKeyResponse;

        $this->cardholderName = $cardholderName;
        $this->cardNumber = $cardNumber;
        $this->expiryDate = $expiryDate;
        $this->securityCode = $securityCode;
    }

    /**
     * An array of arrays, each containing the attributes required for the HTML
     * input elements in the payment form.
     *
     * TODO: have a think about this. A HTML element object could be very useful here
     * to formalise the data needed for constructing the HTML front end in a number of
     * different places.
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

    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    public function getSecurityCode()
    {
        return $this->securityCode;
    }

    /**
     * The full URL of this resource.
     */
    public function getUrl()
    {
        return $this->auth->getUrl($this->getResourcePath());
    }

    /**
     * Get the message body data as an array.
     */
    public function getBody()
    {
        return [
            'cardDetails' => [
                'cardholderName' => $this->getCardholderName(),
                'cardNumber' => $this->getCardNumber(),
                'expiryDate' => $this->getExpiryDate(),
                'securityCode' => $this->getSecurityCode(),
            ],
        ];
    }

    /**
     * Get the message header data as an array.
     * This request does not use the HTTP Basic Auth, but the temporary session
     * key token instead. This is because it will accessible to end users, and
     * the secure integration key and password cannot be exposed here.
     */
    public function getHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->sessionKeyResponse->getMerchantSessionKey(),
        ];
    }
}
