<?php

namespace Academe\SagePay\Psr7\Request;

/**
 * Abstract for shared functionality across "instructions" requests.
 */

use Academe\SagePay\Psr7\Model\Auth;
use Academe\SagePay\Psr7\Model\Endpoint;

abstract class AbstractInstruction extends AbstractRequest
{
    protected $transactionId;
    protected $instructionType;

    protected $resource_path = ['transactions', '{transactionId}', 'instructions'];

    /**
     * @param Endpoint $endpoint
     * @param Auth $auth
     * @param string $transactionId The ID of the transaction to void
     */
    public function __construct(Endpoint $endpoint, Auth $auth, $transactionId)
    {
        $this->setEndpoint($endpoint);
        $this->setAuth($auth);

        $this->transactionId = $transactionId;
    }

    /**
     * Get the message body data for serializing.
     * @return array
     */
    public function jsonSerialize()
    {
        $body = [];

        if (!empty($this->getInstructionType())) {
            $body['instructionType'] = $this->getInstructionType();
        }

        return $body;
    }

    public function getInstructionType()
    {
        return $this->instructionType;
    }

    /**
     * Getter used to construct the URL.
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }
}
