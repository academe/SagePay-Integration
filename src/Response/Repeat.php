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
     * TODO: paymentMethod is returned with this response.
     */
    protected function setData($data, $httpCode)
    {
        $this->setHttpCode($this->deriveHttpCode($httpCode, $data));

        $this->transactionId            = Helper::dataGet($data, 'transactionId', null);
        $this->transactionType          = Helper::dataGet($data, 'transactionType', null);

        $this->setStatuses($data);

        $this->retrievalReference       = Helper::dataGet($data, 'retrievalReference', null);
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
     * The authorisation code returned from your merchant bank.
     * @return mixed
     */
    public function getBankAuthorisationCode()
    {
        return $this->bankAuthorisationCode;
    }

    /**
     * @inheritdoc
     */
    public static function isResponse(array $data)
    {
        return !empty(Helper::dataGet($data, 'transactionId'))
            && Helper::dataGet($data, 'transactionType') == AbstractRequest::TRANSACTION_TYPE_REPEAT;
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
