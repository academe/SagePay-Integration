<?php

namespace Academe\SagePay\Psr7\Request;

/**
 * The "release" instruction request.
 * Release a deferred transaction so funds can be collected.
 */

use Academe\SagePay\Psr7\Money\AmountInterface;
use Academe\SagePay\Psr7\Model\Auth;
use Academe\SagePay\Psr7\Model\Endpoint;

class CreateRelease extends AbstractInstruction
{
    protected $instructionType = AbstractRequest::INSTRUCTION_TYPE_RELEASE;

    // An amount is required, UP TO the total amount deferred.
    protected $amount;

    /**
     * @param Endpoint $endpoint
     * @param Auth $auth
     * @param string $transactionId The ID of the transaction to void
     */
    public function __construct(Endpoint $endpoint, Auth $auth, $transactionId, AmountInterface $amount)
    {
        parent::__construct($endpoint, $auth, $transactionId);

        $this->amount = $amount;
    }

    /**
     * Get the message body data for serializing.
     * @return array
     */
    public function jsonSerialize()
    {
        $body = parent::jsonSerialize();

        $body['amount'] = $this->amount->getAmount();

        return $body;
    }
}
