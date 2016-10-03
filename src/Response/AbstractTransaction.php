<?php

namespace Academe\SagePay\Psr7\Response;

/**
 * Shared transaction response abstract.
 * TODO: create a fromHttpResponse() that returns ANY of the supported transaction responses.
 */

abstract class AbstractTransaction extends AbstractResponse
{
    /**
     * Transaction status from Sage Pay.
     */
    const STATUS_OK         = 'Ok';
    const STATUS_NOTAUTHED  = 'NotAuthed';
    const STATUS_REJECTED   = 'Rejected';
    const STATUS_3DAUTH     = '3DAuth';
    const STATUS_MALFORMED  = 'Malformed';
    const STATUS_INVALID    = 'Invalid';
    const STATUS_ERROR      = 'Error';

    /**
     * The status, statusCode and statusReason are used in most response messages.
     */
    protected $status;
    protected $statusCode;
    protected $statusDetail;

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
     * Convenient serialisation for logging and debugging.
     * Each response message would extend this where appropriate.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $return = [
            'httpCode' => $this->getHttpCode(),
            'status' => $this->getStatus(),
            'statusCode' => $this->getStatusCode(),
            'statusDetail' => $this->getStatusDetail(),
        ];

        return $return;
    }
}
