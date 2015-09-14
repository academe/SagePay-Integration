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

    // FIXME: the docs say "OK" but the API returns "Ok".
    protected $statuses = [
        'ok' => 'OK',
        'notauthed' => 'NotAuthed',
        'rejected' => 'Rejected',
        '3dauth' => '3DAuth',
        'malformed' => 'Malformed',
        'invalid' => 'Invalid',
        'error' => 'Error',
    ];

    public function __construct(
        $transactionID,
        // Payment, Refund etc.
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
}
