<?php namespace Academe\SagePay\Message;

/**
 * The request for fetching a copy of a session key, to check its validity.
 * The response will be a SessionKeyResponse message.
 */

use Exception;
use UnexpectedValueException;

class SessionKeyValidateRequest
{
    protected $sessionKey;

    /**
     * Supply the previously provided SessionKeyResponse for validation.
     */
    public function __construct(SessionKeyResponse $sessionKey)
    {
        $this->sessionKey = $sessionKey
    }

    /**
     * The merchantSessionKey will be sent as a URL parameter.
     * This message to SagePay has no body otherwise, and no authorisation is required.
     */
    public function getMerchantSessionKey()
    {
        return $this->sessionKey->getMerchantSessionKey();
    }
}
