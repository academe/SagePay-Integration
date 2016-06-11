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

class Payment extends AbstractResponse
{
    protected $transactionId;
    protected $transactionType;

    protected $retrievalReference;
    protected $bankResponseCode;
    protected $bankAuthorisationCode;

    protected $secure3D;
    protected $paymentMethod;

    /**
     * @param ResponseInterface $message
     * @internal param array|object|ResponseInterface $data
     */
    public function __construct(ResponseInterface $message = null)
    {
        if (isset($message)) {
            $data = $this->parseBody($message);
            $this->setData($data, $message->getStatusCode());
        }
    }

    /**
     * @param $data
     * @param $httpCode
     * @return $this
     */
    protected function setData($data, $httpCode)
    {
        // Check we are not trying to shoehorn in a 3D Secure Redirect
        if (Secure3DRedirect::isResponse($data)) {
            throw new UnexpectedValueException('3DSecure redirect response detected; use Response\Secure3DRedirect class');
        }

        $this->setHttpCode($this->deriveHttpCode($httpCode, $data));

        // Note the resource is called "3DSecure" and not "Secure3D" as used
        // for valid class, method and variable names.

        if (Secure3D::isResponse($data)) {
            // Create a 3DSecure object from the array data.
            $this->secure3D = Secure3D::fromData($data, $httpCode);
        } else {
            // Just take whatever was given, if anything.
            $this->secure3D = Helper::dataGet($data, '3DSecure', null);
        }

        if (PaymentMethod::isResponse($data)) {
            // Create a PaymentMethod object from the array data.
            $this->paymentMethod = PaymentMethod::fromData($data, $httpCode);
        }

        $this->transactionId            = Helper::dataGet($data, 'transactionId', null);
        $this->transactionType          = Helper::dataGet($data, 'transactionType', null);

        $this->setStatuses($data);

        $this->retrievalReference       = Helper::dataGet($data, 'retrievalReference', null);
        $this->bankResponseCode         = Helper::dataGet($data, 'bankResponseCode', null);
        $this->bankAuthorisationCode    = Helper::dataGet($data, 'bankAuthorisationCode', null);

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
     * Determine whether the response data looks like this kind of message.
     *
     * @param array $data Response message body data.
     * @return boolean True if the data matches this kind of response.
     */
    public static function isResponse(array $data)
    {
        return !empty(Helper::dataGet($data, 'transactionId'))
            && Helper::dataGet($data, 'transactionType') == AbstractRequest::TRANSACTION_TYPE_PAYMENT;
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
        return [
            'transactionId' => $this->transactionId,
            'transactionType' => $this->transactionType,
            'status' => $this->status,
            'statusCode' => $this->statusCode,
            'statusDetail' => $this->statusDetail,
            'retrievalReference' => $this->retrievalReference,
            'bankResponseCode' => $this->bankResponseCode,
            'bankAuthorisationCode' => $this->bankAuthorisationCode,
            'secure3D' => $this->secure3D,
        ];
    }
}
