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
use Academe\SagePay\Psr7\Request\AbstractRequest;

class Repeat extends AbstractTransaction
{
    protected $transactionId;
    protected $transactionType;

    protected $retrievalReference;
    protected $bankResponseCode;
    protected $bankAuthorisationCode;

    protected $paymentMethod;

    /**
     * @param $data
     * @return $this
     * TODO: paymentMethod is returned with this response.
     */
    protected function setData($data)
    {
        $this->transactionId            = Helper::dataGet($data, 'transactionId', null);
        $this->transactionType          = Helper::dataGet($data, 'transactionType', null);

        $this->retrievalReference       = Helper::dataGet($data, 'retrievalReference', null);
        $this->bankAuthorisationCode    = Helper::dataGet($data, 'bankAuthorisationCode', null);

        $paymentMethod = Helper::dataGet($data, 'paymentMethod');
        if ($paymentMethod) {
            // Create a PaymentMethod object from the array data.
            $this->paymentMethod = PaymentMethod::fromData($paymentMethod, $this->getHttpCode());
        }

        return $this;
    }

    /**
     * The ID given to the transaction by Sage Pay.
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * The type of the transaction.
     * @return mixed
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * Sage Pay unique Authorisation Code for a successfully authorised
     * transaction. Only present if Status is OK (or Ok).
     * @return mixed
     */
    public function getRetrievalReference()
    {
        return $this->retrievalReference;
    }

    /**
     * The authorisation code returned from your merchant bank.
     * @return mixed
     */
    public function getBankAuthorisationCode()
    {
        return $this->bankAuthorisationCode;
    }

    /**
     * @return PaymentMethod|null The payment method object, if available.
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Convenient serialisation for logging and debugging.
     * @return array
     */
    public function jsonSerialize()
    {
        $return = parent::jsonSerialize();

        $return['transactionId'] = $this->transactionId;
        $return['transactionType'] = $this->transactionType;

        $return['retrievalReference'] = $this->retrievalReference;
        $return['bankAuthorisationCode'] = $this->bankAuthorisationCode;

        if ($paymentMethod = $this->getPaymentMethod()) {
            $return['paymentMethod'] = $paymentMethod;
        }

        return $return;
    }
}
