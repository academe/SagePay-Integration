<?php

namespace Academe\SagePay\Psr7\Request;

/**
 * Request for linking a security code to a saved cardIdentifier.
 * Allows a security code to be captured and linked to a saved card identifier
 * for just one transaction, for additional security. Sage Pay will then throw it
 * away.
 */

use Academe\SagePay\Psr7\Model\Auth;
use Academe\SagePay\Psr7\Model\Endpoint;
//use Academe\SagePay\Psr7\Response\SessionKey as SessionKeyResponse;
//use Academe\SagePay\Psr7\Security\SensitiveValue;

class SecurityCode extends AbstractRequest
{
    protected $resource_path = ['card-identifiers', '{cardIdentifier}', 'security-code'];

    protected $cardIdentifier;

    /**
     * @param Endpoint $endpoint
     * @param Auth $auth
     * @param SessionKeyResponse $sessionKey
     * @param string $cardIdentifier
     * @param string $securityCode
     */
    public function __construct(
        Endpoint $endpoint,
        Auth $auth,
        SessionKeyResponse $sessionKey,
        $cardIdentifier,
        $securityCode
    ) {
        $this->setEndpoint($endpoint);
        $this->setAuth($auth);
        $this->sessionKey = $sessionKey;

        $this->cardIdentifier = $cardIdentifier;
        $this->securityCode = new SensitiveValue($securityCode);
    }

    /**
     * @return SensitiveValue|mixed
     */
    public function getSecurityCode()
    {
        return $this->securityCode ? $this->securityCode->peek() : $this->securityCode;
    }

    /**
     *
     */
    public function getCardIdentifier()
    {
        return $this->cardIdentifier;
    }
}
