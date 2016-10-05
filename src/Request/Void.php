<?php

namespace Academe\SagePay\Psr7\Request;

/**
 * This is the first of the "instructions" requests.
 * When further instructions are introduced, much functionality here is likely
 * to be moved out to an AbstractInstruction class.
 */

use Academe\SagePay\Psr7\Model\Auth;
use Academe\SagePay\Psr7\Model\Endpoint;

class Void extends AbstractRequest
{
    protected $transactionId;
    protected $instructionType = 'void';

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
        return [
            'instructionType' => $this->getInstructionType(),
        ];
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
