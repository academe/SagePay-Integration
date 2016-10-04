<?php

namespace Academe\SagePay\Psr7\Response;

/**
 * Shared transaction response abstract.
 */

use Academe\SagePay\Psr7\Helper;
use Academe\SagePay\Psr7\Money\Amount;
use Academe\SagePay\Psr7\Money\Currency;

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
     * The status, statusCode and statusReason are used in all transaction responses.
     */
    protected $status;
    protected $statusCode;
    protected $statusDetail;

    protected $transactionId;
    protected $transactionType;

    protected $retrievalReference;
    protected $bankResponseCode;
    protected $bankAuthorisationCode;

    protected $secure3D;
    protected $paymentMethod;

    protected $currency;

    protected $totalAmount;
    protected $saleAmount;
    protected $surchargeAmount;

    /**
     * @param $data
     * @return $this
     */
    protected function setData($data)
    {
        // Note the resource is called "3DSecure" and not "Secure3D" as used
        // for valid class, method and variable names.

        $this->transactionId = Helper::dataGet($data, 'transactionId', null);
        $this->transactionType = Helper::dataGet($data, 'transactionType', null);

        $this->retrievalReference = Helper::dataGet($data, 'retrievalReference', null);
        $this->bankResponseCode = Helper::dataGet($data, 'bankResponseCode', null);
        $this->bankAuthorisationCode = Helper::dataGet($data, 'bankAuthorisationCode', null);

        // Common fields.
        $this->setPaymentMethod($data);
        $this->setStatuses($data);
        $this->set3dSecure($data);
        $this->setAmount($data);

        return $this;
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
     * Set the three status fields from body data.
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

    protected function set3dSecure($data)
    {
        $secure3D = Helper::dataGet($data, '3DSecure');

        if ($secure3D) {
            // Create a 3DSecure object from the array data.
            $this->secure3D = Secure3D::fromData($secure3D);
        }
    }

    protected function setAmount($data)
    {
        // Optional "amount" and "currency", available only when fetching an
        // existing payment from Sage Pay.

        if (($currency = Helper::dataGet($data, 'currency')) != null) {
            $this->currency = new Currency($currency);

            // Only get the amounts if we have a currency to assign to them.

            if (($totalAmount = Helper::dataGet($data, 'amount.totalAmount')) !== null) {
                $this->totalAmount = new Amount($this->currency, $totalAmount);
            }

            if (($saleAmount = Helper::dataGet($data, 'amount.saleAmount')) !== null) {
                $this->saleAmount = new Amount($this->currency, $saleAmount);
            }

            if (($surchargeAmount = Helper::dataGet($data, 'amount.surchargeAmount')) !== null) {
                $this->surchargeAmount = new Amount($this->currency, $surchargeAmount);
            }
        }
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

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    public function getSaleAmount()
    {
        return $this->saleAmount;
    }

    public function getSurchargeAmount()
    {
        return $this->surchargeAmount;
    }

    /**
     * @return PaymentMethod|null The payment method object, if available.
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
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
     * Convenient serialisation for logging and debugging.
     * Each response message would extend this where appropriate.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $return = [];

        // Transaction context.
        $return['transactionId'] = $this->transactionId;
        $return['transactionType'] = $this->transactionType;

        // Status details.
        $return['httpCode'] = $this->getHttpCode();
        $return['status'] = $this->getStatus();
        $return['statusCode'] = $this->getStatusCode();
        $return['statusDetail'] = $this->getStatusDetail();

        if (($retrievalReference = $this->getRetrievalReference()) !== null) {
            $return['retrievalReference'] = $retrievalReference;
        }

        if (($bankResponseCode = $this->getBankResponseCode()) !== null) {
            $return['bankResponseCode'] = $bankResponseCode;
        }

        if (($bankAuthorisationCode = $this->getBankAuthorisationCode()) !== null) {
            $return['bankAuthorisationCode'] = $bankAuthorisationCode;
        }

        if ($paymentMethod = $this->getPaymentMethod()) {
            $return['paymentMethod'] = $paymentMethod;
        }

        if ($secure3D = $this->get3DSecure()) {
            $return['3DSecure'] = $secure3D;
        }

        $amount = [];

        if (($totalAmount = $this->getTotalAmount()) !== null) {
            $amount['totalAmount'] = $totalAmount->getAmount();
        }

        if (($saleAmount = $this->getSaleAmount()) !== null) {
            $amount['saleAmount'] = $saleAmount->getAmount();
        }

        if (($surchargeAmount = $this->getSurchargeAmount()) !== null) {
            $amount['surchargeAmount'] = $surchargeAmount->getAmount();
        }

        if (! empty($amount)) {
            $return['amount'] = $amount;
        }

        if ($currency = $this->getCurrency()) {
            $return['currency'] = $currency->getCode();
        }

        return $return;
    }
}
