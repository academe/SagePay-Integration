<?php namespace Academe\SagePay\Psr7\Request;

/**
 * The request for a session key.
 */

use Academe\SagePay\Psr7\Model\Auth;
use Academe\SagePay\Psr7\AbstractMessage;
use Academe\SagePay\Psr7\Factory\FactoryInterface;

class SessionKey extends AbstractRequest
{
    protected $resource_path = ['merchant-session-keys'];

    public function __construct(Auth $auth, FactoryInterface $factory = null)
    {
        $this->auth = $auth;
        $this->factory = $factory;
    }

    /**
     * The vendorName goes into the request body.
     * The integrationKey and integrationPassword is used as HTTP Basic Auth credentials.
     */
    public function getAuth()
    {
        return $this->auth;
    }

    public function getBody()
    {
        return ['vendorName' => $this->auth->getVendorName()];
    }

    /**
     * The HTTP Basic Auth header, as an array.
     * Use this if your transport tool does not do "Basic Auth" out of the box.
     */
    public function getHeaders()
    {
        return $this->getBasicAuthHeaders();
    }
}
