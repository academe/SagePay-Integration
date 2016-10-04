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
     * @param $data
     * @return $this
     */
    protected function setData($data)
    {
        // Note the resource is called "3DSecure" and not "Secure3D" as used
        // for valid class, method and variable names.

        $this->transactionId = Helper::dataGet($data, 'transactionId', null);
        $this->transactionType = Helper::dataGet($data, 'transactionType', null);

        $this->retrievalReference = Helper::dataGet($data, 'retrievalReference', null);
        $this->bankResponseCode = Helper::dataGet($data, 'bankResponseCode', null);
        $this->bankAuthorisationCode = Helper::dataGet($data, 'bankAuthorisationCode', null);

        // Common fields.
        $this->setPaymentMethod($data);
        $this->setStatuses($data);
        $this->set3dSecure($data);
        $this->setAmount($data);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isSuccess()
    {
        return $this->getStatus() == static::STATUS_OK;
    }

    /**
     * Convenient serialisation for logging and debugging.
     * @return array
     */
    public function jsonSerialize()
    {
        $return = parent::jsonSerialize();

        return $return;
    }
}
