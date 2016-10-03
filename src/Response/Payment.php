<?php

namespace Academe\SagePay\Psr7\Response;

/**
 * Result of a Payment request where payment is approved or declined.
 * See Secrure3DRedirect for when the result is 3D Secure redirect.
 */

use Academe\SagePay\Psr7\Helper;
use Psr\Http\Message\ResponseInterface;
use Academe\SagePay\Psr7\Request\AbstractRequest;
use UnexpectedValueException;

class Payment extends AbstractTransaction
{
    protected $transactionId;
    protected $transactionType;

    protected $retrievalReference;
    protected $bankResponseCode;
    protected $bankAuthorisationCode;

    protected $secure3D;
    protected $paymentMethod;

    /**
     * @param $data
     * @return $this
     */
    protected function setData($data)
    {
        // Note the resource is called "3DSecure" and not "Secure3D" as used
        // for valid class, method and variable names.

        $secure3D = Helper::dataGet($data, '3DSecure');
        if ($secure3D) {
            // Create a 3DSecure object from the array data.
            $this->secure3D = Secure3D::fromData(Helper::dataGet($secure3D, '3DSecure'));
        }

        $paymentMethod = Helper::dataGet($data, 'paymentMethod');
        if ($paymentMethod) {
            // Create a PaymentMethod object from the array data.
            $this->paymentMethod = PaymentMethod::fromData($paymentMethod, $this->getHttpCode());
        }

        $this->transactionId = Helper::dataGet($data, 'transactionId', null);
        $this->transactionType = Helper::dataGet($data, 'transactionType', null);

        $this->retrievalReference = Helper::dataGet($data, 'retrievalReference', null);
        $this->bankResponseCode = Helper::dataGet($data, 'bankResponseCode', null);
        $this->bankAuthorisationCode = Helper::dataGet($data, 'bankAuthorisationCode', null);

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
     * Also known as the decline code, these are codes that are
     * specific to the merchant bank. 
     * @return mixed
     */
    public function getBankResponseCode()
    {
        return $this->bankResponseCode;
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
     * The 3D Secure object.
     * @return mixed
     */
    public function get3DSecure()
    {
        return $this->secure3D;
    }

    /**
     * @return Secure3D|null The 3D Secure final status object, if available.
     */
    public function get3DSecureStatus()
    {
        if (isset($this->secure3D)) {
            return $this->secure3D->getStatus();
        }

        return null;
    }

    /**
     * @return PaymentMethod|null The payment method object, if available.
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
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

        $return['transactionId'] = $this->transactionId;
        $return['transactionType'] = $this->transactionType;
        $return['retrievalReference'] = $this->retrievalReference;
        $return['bankResponseCode'] = $this->bankResponseCode;
        $return['bankAuthorisationCode'] = $this->bankAuthorisationCode;
        $return['secure3D'] = $this->secure3D;

        if ($paymentMethod = $this->getPaymentMethod()) {
            $return['paymentMethod'] = $paymentMethod;
        }

        return $return;
    }
}
