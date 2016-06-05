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

    protected $status;
    protected $statusCode;
    protected $statusDetail;

    protected $retrievalReference;
    protected $bankResponseCode;
    protected $bankAuthorisationCode;

    protected $Secure3D;

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
        if (Helper::dataGet($data, 'statusCode') == '2007' && Helper::dataGet($data, 'status') == AbstractResponse::STATUS_3DAUTH) {
            throw new UnexpectedValueException('3DSecure redirect response detected; use Response\Secure3DRedirect class');
        }

        $this->setHttpCode($this->deriveHttpCode($httpCode, $data));

        // Note the resource is called "3DSecure" and not "Secure3D" as used
        // for valid class, method and variable names.

        $Secure3D = Helper::dataGet($data, '3DSecure', null);

        if ($Secure3D instanceof Secure3D) {
            // A 3DSecure object has already been put together.
        } elseif (is_array($Secure3D)) {
            // Create a 3DSecure object from the array data.
            $Secure3D = Secure3D::fromData($data);
        } elseif (is_null($Secure3D)) {
            // No 3D Secure object. Not all transaction types involve 3D Secure.
        } else {
            // Don't know how to handle this data.
            // TODO: Exception.
        }

        $this->transactionId            = Helper::dataGet($data, 'transactionId', null);
        $this->transactionType          = Helper::dataGet($data, 'transactionType', null);
        $this->status                   = Helper::dataGet($data, 'status', null);
        $this->statusCode               = Helper::dataGet($data, 'statusCode', null);
        $this->statusDetail             = Helper::dataGet($data, 'statusDetail', null);
        $this->retrievalReference       = Helper::dataGet($data, 'retrievalReference', null);
        $this->bankResponseCode         = Helper::dataGet($data, 'bankResponseCode', null);
        $this->bankAuthorisationCode    = Helper::dataGet($data, 'bankAuthorisationCode', null);
        $this->Secure3D                 = $Secure3D;

        return $this;
    }

    /**
     * There is a status (e.g. Ok), a statusCode (e.g. 2007), and a statusDetail (e.g. Transaction authorised).
     * Also there is a HTTP return code (e.g. 202). All are needed in different contexts.
     * However, there is a hint that the "status" may be removed, relying on the HTTP return code instead.
     * @return string The overall status string of the transaction.
     */
    public function getStatus()
    {
        // Enforce the correct capitalisation.

        $statusValue = $this->constantValue('STATUS', $this->status);

        return ! empty($statusValue) ? $statusValue : $this->status;
    }

    /**
     * @return string The numeric code that represents the status detail.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * This message in some range of codes can be presented to the end user.
     * In other ranges of codes it should only ever be logged fot the site administrator.
     * @return string The detailed status message.
     */
    public function getStatusDetail()
    {
        return $this->statusDetail;
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
        return $this->Secure3D;
    }

    /**
     * @return Secure3D The 3D Secure final status object, if available.
     */
    public function get3DSecureStatus()
    {
        if (isset($this->Secure3D)) {
            return $this->Secure3D->getStatus();
        }

        return null;
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
            'Secure3D' => $this->Secure3D,
        ];
    }
}
