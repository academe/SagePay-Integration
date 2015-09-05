<?php namespace Academe\SagePayJs\Message;

/**
 * The request for a session key.
 */

use Academe\SagePayJs\Models\Auth;

class SessionKeyRequest
{
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
}
