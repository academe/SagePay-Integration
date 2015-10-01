<?php namespace Academe\SagePayMsg\Message;

/**
 * There will be completely different transaction response
 * messages, depending upon the request that was made. This
 * class (maybe to be made an abstract) contains what is
 * common for the responses.
 *
 * TODO: if the status is "3DAuth" then a URL will be passed back from SagePay.
 * This is not documented for the API yet and not supported anyway for API v1.
 */

use Exception;
use UnexpectedValueException;

use Academe\SagePayMsg\Helper;

class TransactionResponse extends AbstractMessage
{
    protected $transactionID;
    protected $trasactionType;
    protected $status;
    protected $statusCode;
    protected $statusDetail;
    protected $retrievalReference;
    protected $bankResponseCode;
    protected $bankAuthorisationCode;

    protected $trasactionTypes = [
        'Payment',
    ];

    // The docs say "OK" but the API returns "Ok".
    protected $statuses = [
        'ok' => 'Ok',
        'notauthed' => 'NotAuthed',
        'rejected' => 'Rejected',
        '3dauth' => '3DAuth',
        'malformed' => 'Malformed',
        'invalid' => 'Invalid',
        'error' => 'Error',
    ];

    public function __construct(
        $transactionID,
        $trasactionType,
        $status,
        $statusCode,
        $statusDetail,
        $retrievalReference,
        $bankResponseCode,
        $bankAuthorisationCode
    ) {
        $this->transactionID = $transactionID;
        $this->trasactionType = $trasactionType;
        $this->status = $status;
        $this->statusCode = $statusCode;
        $this->statusDetail = $statusDetail;
        $this->retrievalReference = $retrievalReference;
        $this->bankResponseCode = $bankResponseCode;
        $this->bankAuthorisationCode = $bankAuthorisationCode;
    }

    public static function fromData($data)
    {
        return new static(
            Helper::structureGet($data, 'transactionID', null),
            Helper::structureGet($data, 'trasactionType', null),
            Helper::structureGet($data, 'status', null),
            Helper::structureGet($data, 'statusCode', null),
            Helper::structureGet($data, 'statusDetail', null),
            Helper::structureGet($data, 'retrievalReference', null),
            Helper::structureGet($data, 'bankResponseCode', null),
            Helper::structureGet($data, 'bankAuthorisationCode', null)
        );
    }

    /**
     * The overall status of the transaction.
     */
    public function getStatus()
    {
        return ! empty($this->statuses[strtolower($this->status)])
            ? $this->statuses[strtolower($this->status)]
            : $this->status;
    }

    /**
     * The code that represents the status detail.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * The detailed status message.
     */
    public function getStatusDetail()
    {
        return $this->statusDetail;
    }

    /**
     * The ID given to the transaction by Sage Pay.
     */
    public function getTransactionID()
    {
        return $this->transactionID;
    }

    /**
     * The type of the transaction.
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * Sage Pay unique Authorisation Code for a successfully authorised
     * transaction. Only present if Status is OK (or Ok).
     */
    public function getRetrievalReference()
    {
        return $this->retrievalReference;
    }

    /**
     * Also known as the decline code, these are codes that are
     * specific to the merchant bank. 
     */
    public function getBankResponseCode()
    {
        return $this->bankResponseCode;
    }

    /**
     * The authorisation code returned from your merchant bank.
     */
    public function getBankAuthorisationCode()
    {
        return $this->bankAuthorisationCode;
    }
}
