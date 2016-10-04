<?php

namespace Academe\SagePay\Psr7\Response;

/**
 * Shared transaction response abstract.
 */

use Academe\SagePay\Psr7\Helper;

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
     * Set the usual three status fields from body data.
     * @param array $data The response message body data.
     * @return null
     */
    protected function setStatuses($data)
    {
        $this->status       = Helper::dataGet($data, 'status', null);
        $this->statusCode   = Helper::dataGet($data, 'statusCode', null);
        $this->statusDetail = Helper::dataGet($data, 'statusDetail', null);
    }

    protected function setPaymentMethod($data)
    {
        $paymentMethod = Helper::dataGet($data, 'paymentMethod');

        if ($paymentMethod) {
            $card = Helper::dataGet($paymentMethod, 'card');

            if ($card) {
                // Create a PaymentMethod object from the array data.
                $this->paymentMethod = Model\Card::fromData($card);
            }
        }
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
