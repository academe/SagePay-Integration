<?php

namespace Academe\SagePay\Psr7\Response;

/**
 * Result of a Payment request where payment is approved or declined.
 * See Secrure3DRedirect for when the result is 3D Secure redirect.
 */

use Academe\SagePay\Psr7\Request\AbstractRequest;
use Psr\Http\Message\ResponseInterface;
use Academe\SagePay\Psr7\Helper;
use UnexpectedValueException;

class Payment extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    public function isSuccess()
    {
        return $this->getStatus() == static::STATUS_OK;
    }
}
