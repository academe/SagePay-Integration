<?php namespace Academe\SagePay\Psr7\Request;

/**
 * The request for fetching a copy of a session key, to check its validity.
 * The response will be a SessionKeyResponse message.
 */

use Academe\SagePay\Psr7\Model\Endpoint;
use Academe\SagePay\Psr7\Response\SessionKey as SessionKeyResponse;

class SessionKeyValidate extends AbstractRequest
{
    protected $resource_path = ['merchant-session-keys', '{merchantSessionKey}'];

    protected $sessionKey;

    /**
     * @var string This message is a GET request
     */
    protected $method = 'GET';

    /**
     * Supply the previously provided SessionKeyResponse for validation.
     * @param Endpoint $endpoint
     * @param SessionKeyResponse $sessionKey
     */
    public function __construct(Endpoint $endpoint, SessionKeyResponse $sessionKey)
    {
        $this->endpoint = $endpoint;
        $this->sessionKey = $sessionKey;
    }

    /**
     * The merchantSessionKey will be sent as a URL parameter.
     * This message to SagePay has no body otherwise, and no authorisation is required.
     */
    public function getMerchantSessionKey()
    {
        return $this->sessionKey->getMerchantSessionKey();
    }

    /**
     * This message has no body.
     */
    public function jsonSerialize()
    {
    }

    /**
     * This message has no authentication headers.
     */
    public function getHeaders()
    {
        return [];
    }

}
