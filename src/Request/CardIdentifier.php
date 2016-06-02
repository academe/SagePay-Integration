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

use Academe\SagePay\Psr7\Model\Auth;
use Academe\SagePay\Psr7\Model\Endpoint;
use Academe\SagePay\Psr7\Response\SessionKey as SessionKeyResponse;
use Academe\SagePay\Psr7\Security\SensitiveValue;

class CardIdentifier extends AbstractRequest
{
    protected $resource_path = ['card-identifiers'];

    protected $sessionKey;

    // Store card details as sensitive information.
    // This won't protect us from JSON serialisation, since that function is needed
    // for constructing messages, but should help protect from other types of serialisation.

    protected $cardholderName;
    protected $cardNumber;
    protected $expiryDate;
    protected $securityCode;

    /**
     * TODO: validation
     * $expiryDate MMYY (maybe convert some common formats).
     * $cardNumber Lunn check.
     * $securityCode Digits only.
     * @param Endpoint $endpoint
     * @param Auth $auth
     * @param SessionKeyResponse $sessionKey
     * @param $cardholderName
     * @param $cardNumber
     * @param $expiryDate
     * @param null $securityCode
     */
    public function __construct(
        Endpoint $endpoint,
        Auth $auth,
        SessionKeyResponse $sessionKey,
        $cardholderName,
        $cardNumber,
        $expiryDate,
        $securityCode = null
    ) {
        $this->setEndpoint($endpoint);
        $this->setAuth($auth);
        $this->sessionKey = $sessionKey;

        $this->cardholderName = new SensitiveValue($cardholderName);
        $this->cardNumber = new SensitiveValue($cardNumber);
        $this->expiryDate = new SensitiveValue($expiryDate);
        $this->securityCode = new SensitiveValue($securityCode);
    }

    /**
     * @return SensitiveValue|mixed
     */
    public function getCardholderName()
    {
        return $this->cardholderName ? $this->cardholderName->peek() : $this->cardholderName;
    }

    /**
     * @return SensitiveValue|mixed
     */
    public function getCardNumber()
    {
        return $this->cardNumber ? $this->cardNumber->peek() : $this->cardNumber;
    }

    /**
     * @return SensitiveValue|mixed
     */
    public function getExpiryDate()
    {
        return $this->expiryDate ? $this->expiryDate->peek() : $this->expiryDate;
    }

    /**
     * @return SensitiveValue|mixed
     */
    public function getSecurityCode()
    {
        return $this->securityCode ? $this->securityCode->peek() : $this->securityCode;
    }

    /**
     * Protect this class from direct JSON serialisation.
     * Replace all card detail characters with asterisks.
     * @return array
     */
    public function jsonSerialize()
    {
        $data = $this->jsonSerializePeek();

        array_walk_recursive($data, function(&$item, $key) {
            if (is_string($item)) {
                $item = str_repeat('*', strlen($item));
            }
        });

        return $data;
    }

    /**
     * Get the message body data for serializing.
     * This is the explicit JSON serialisation method, not called up during debug.
     * @return array
     */
    public function jsonSerializePeek()
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
     * @return array
     */
    public function getHeaders()
    {
        return $this->sessionKey->getAuthHeaders();
    } 
}
