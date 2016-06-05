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

class Repeat extends AbstractResponse
{
    protected $transactionId;
    protected $transactionType;

    protected $status;
    protected $statusCode;
    protected $statusDetail;

    protected $retrievalReference;
    protected $bankResponseCode;
    protected $bankAuthorisationCode;

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
        $this->setHttpCode($this->deriveHttpCode($httpCode, $data));

        $this->transactionId            = Helper::dataGet($data, 'transactionId', null);
        $this->transactionType          = Helper::dataGet($data, 'transactionType', null);
        $this->status                   = Helper::dataGet($data, 'status', null);
        $this->statusCode               = Helper::dataGet($data, 'statusCode', null);
        $this->statusDetail             = Helper::dataGet($data, 'statusDetail', null);
        $this->retrievalReference       = Helper::dataGet($data, 'retrievalReference', null);
        $this->bankAuthorisationCode    = Helper::dataGet($data, 'bankAuthorisationCode', null);

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
     * The authorisation code returned from your merchant bank.
     * @return mixed
     */
    public function getBankAuthorisationCode()
    {
        return $this->bankAuthorisationCode;
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
            'bankAuthorisationCode' => $this->bankAuthorisationCode,
        ];
    }
}
