<?php namespace Academe\SagePayJs\Message;

/**
 * The request for a session key.
 */

use Academe\SagePayJs\Models\Auth;

class SessionKeyRequest extends AbstractMessage
{
    protected static $resource_path = 'merchant-session-keys';

    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * The vendorName goes into the request body.
     * The integrationKey and integrationPassword is used as HTTP Basic Auth credentials.
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * The path of this resource.
     */
    public function getResourcePath()
    {
        return static::$resource_path;
    }

    /**
     * The full URL of this resource.
     */
    public function getUrl()
    {
        return $this->auth->getUrl($this->getResourcePath());
    }

    public function getIntegrationKey()
    {
        return $this->auth->getIntegrationKey();
    }

    public function getIntegrationPassword()
    {
        return $this->auth->getIntegrationPassword();
    }

    public function getBody()
    {
        return ['vendorName' => $this->auth->getVendorName()];
    }
}
