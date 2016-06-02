<?php

namespace Academe\SagePay\Psr7\Request;

/**
 * The request for a session key.
 * See https://test.sagepay.com/documentation/#merchant-session-keys
 */

use Academe\SagePay\Psr7\Model\Auth;
use Academe\SagePay\Psr7\Model\Endpoint;

class SessionKey extends AbstractRequest
{
    protected $resource_path = ['merchant-session-keys'];

    /**
     * @param Endpoint $endpoint
     * @param Auth $auth
     */
    public function __construct(Endpoint $endpoint, Auth $auth)
    {
        $this->endpoint = $endpoint;
        $this->auth = $auth;
    }

    /**
     * The HTTP Basic Auth header, as an array.
     * Use this if your transport tool does not do "Basic Auth" out of the box.
     * @return array
     */
    public function getHeaders()
    {
        return $this->getBasicAuthHeaders();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'vendorName' => $this->getAuth()->getVendorName(),
        ];
    }
}
