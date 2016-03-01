<?php namespace Academe\SagePay\Psr7\Request;

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

use Academe\SagePay\Psr7\Model\Auth;
use Academe\SagePay\Psr7\Model\Endpoint;
use Academe\SagePay\Psr7\AbstractMessage;
use Academe\SagePay\Psr7\Factory\FactoryInterface;

use Academe\SagePay\Psr7\Response\SessionKey as SessionKeyResponse;

class CardIdentifier extends AbstractRequest
{
    protected $resource_path = ['card-identifiers'];

    protected $auth;
    protected $sessionKey;

    // TODO: store card details as sensitive information.
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
    public function __construct(
        Endpoint $endpoint,
        Auth $auth,
        SessionKeyResponse $sessionKey,
        $cardholderName,
        $cardNumber,
        $expiryDate,
        $securityCode = null,
        FactoryInterface $factory = null
    ) {
        $this->endpoint = $endpoint;
        $this->auth = $auth;
        $this->sessionKey = $sessionKey;
        $this->factory = $factory;

        $this->cardholderName = $cardholderName;
        $this->cardNumber = $cardNumber;
        $this->expiryDate = $expiryDate;
        $this->securityCode = $securityCode;
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
     * Get the message body data for serializing.
     */
    public function jsonSerialize()
    {
        $data = [
            'cardDetails' => [
                'cardholderName' => $this->getCardholderName(),
                'cardNumber' => $this->getCardNumber(),
                'expiryDate' => $this->getExpiryDate(),
            ],
        ];

        // The security code is optional, so only provide it if it has been set.

        if ( ! empty($this->getSecurityCode())) {
            $data['cardDetails']['securityCode'] = $this->getSecurityCode();
        }

        return $data;
    }

    /**
     * Get the message header data as an array.
     * This request does not use the HTTP Basic Auth, but the temporary session
     * key token instead. This is because it will accessible to end users, and
     * the secure integration key and password cannot be exposed here.
     */
    public function getHeaders()
    {
        return $this->sessionKey->getAuthHeaders();
    } 
}
