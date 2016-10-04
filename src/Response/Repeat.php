<?php namespace Academe\SagePay\Psr7\Response;

/**
 * At the moment (12-11-2015 BETA), this resource is the result of a
 * transaction request. It is *not* the details of the transaction
 * that was sent.
 * There is one sub-resource, the Secure3D object, that will be included
 * with this resource automatically so long as the 3D Secure process is
 * final (i.e. no more actions required).
 */

use Academe\SagePay\Psr7\Helper;
use Psr\Http\Message\ResponseInterface;

class Repeat extends AbstractTransaction
{
    /**
     * @param $data
     * @return $this
     */
    protected function setData($data)
    {
        $this->transactionId            = Helper::dataGet($data, 'transactionId', null);
        $this->transactionType          = Helper::dataGet($data, 'transactionType', null);

        $this->retrievalReference       = Helper::dataGet($data, 'retrievalReference', null);
        $this->bankResponseCode         = Helper::dataGet($data, 'bankResponseCode', null);
        $this->bankAuthorisationCode    = Helper::dataGet($data, 'bankAuthorisationCode', null);

        $this->setPaymentMethod($data);
        $this->setStatuses($data);
        $this->set3dSecure($data);
        $this->setAmount($data);

        return $this;
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
