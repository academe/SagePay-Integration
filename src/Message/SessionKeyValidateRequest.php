<?php namespace Academe\SagePayMsg\Message;

/**
 * The request for fetching a copy of a session key, to check its validity.
 * The response will be a SessionKeyResponse message.
 */

use Exception;
use UnexpectedValueException;

use Academe\SagePayMsg\Model\Auth;

class SessionKeyValidateRequest extends AbstractRequest
{
    protected $resource_path = ['merchant-session-keys'];

    protected $auth;
    protected $sessionKey;

    /**
     * Supply the previously provided SessionKeyResponse for validation.
     * TODO: some stuff to fix here. The URL construction is in Auth, but this endpoint
     * does not actually require any authorisation. The URL construction should be done
     * somewhere that can cater for both scenarios.
     */
    public function __construct(Auth $auth, SessionKeyResponse $sessionKey)
    {
        $this->auth = $auth;
        $this->sessionKey = $sessionKey;
    }

    /**
     * The path of this resource.
     *
     * @return array The components of the path.
     */
    public function getResourcePath()
    {
        return array_merge($this->resource_path, [$this->getMerchantSessionKey()]);
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
