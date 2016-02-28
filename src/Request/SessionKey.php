<?php namespace Academe\SagePay\Psr7\Request;

/**
 * The request for a session key.
 * See https://test.sagepay.com/documentation/#merchant-session-keys
 */

use Academe\SagePay\Psr7\Model\Auth;
use Academe\SagePay\Psr7\Model\Endpoint;
use Academe\SagePay\Psr7\AbstractMessage;
use Academe\SagePay\Psr7\Factory\FactoryInterface;

class SessionKey extends AbstractRequest
{
    protected $resource_path = ['merchant-session-keys'];

    public function __construct(Endpoint $endpoint, Auth $auth, FactoryInterface $factory = null)
    {
        $this->endpoint = $endpoint;
        $this->auth = $auth;
        $this->factory = $factory;
    }

    /**
     * The HTTP Basic Auth header, as an array.
     * Use this if your transport tool does not do "Basic Auth" out of the box.
     */
    public function getHeaders()
    {
        return $this->getBasicAuthHeaders();
    }

    public function jsonSerialize()
    {
        return [
            'vendorName' => $this->getAuth()->getVendorName(),
        ];
    }
}
