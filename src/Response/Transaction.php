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

class Transaction extends AbstractResponse
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
    protected $acsUrl;
    protected $paReq;

    const STATUS_OK         = 'Ok';
    const STATUS_NOTAUTHED  = 'NotAuthed';
    const STATUS_REJECTED   = 'Rejected';
    const STATUS_3DAUTH     = '3DAuth';
    const STATUS_MALFORMED  = 'Malformed';
    const STATUS_INVALID    = 'Invalid';
    const STATUS_ERROR      = 'Error';

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

    protected function setData($data, $httpCode)
    {
        $this->setHttpCode($this->deriveHttpCode($httpCode, $data));

        // Note the resource is called "3DSecure" and not "Secure3D" that use
        // for valid class, method and variable names.
        $Secure3D = Helper::structureGet($data, '3DSecure', null);

        if ($Secure3D instanceof Secure3DResponse) {
            // A 3DSecure object has already been put together.
        } elseif (is_array($Secure3D)) {
            // Create a 3DSecure object from the array data.
            $Secure3D = Secure3DResponse::fromData($data);
        } elseif (is_null($Secure3D)) {
            // No 3D Secure object; the 3D Secure part of the transactino is
            // not yet complete.
        } else {
            // Don't know how to handle this data.
            // TODO: Exception.
        }

        $this->transactionId = Helper::structureGet($data, 'transactionId', null);
        $this->transactionType = Helper::structureGet($data, 'transactionType', null);
        $this->status = Helper::structureGet($data, 'status', null);
        $this->statusCode = Helper::structureGet($data, 'statusCode', null);
        $this->statusDetail = Helper::structureGet($data, 'statusDetail', null);
        $this->retrievalReference = Helper::structureGet($data, 'retrievalReference', null);
        $this->bankResponseCode = Helper::structureGet($data, 'bankResponseCode', null);
        $this->bankAuthorisationCode = Helper::structureGet($data, 'bankAuthorisationCode', null);
        $this->Secure3D = $Secure3D;
        $this->acsUrl = Helper::structureGet($data, 'acsUrl', null);
        $this->paReq = Helper::structureGet($data, 'paReq', null);

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
     */
    public function getTransactionId()
    {
        return $this->transactionId;
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

    /**
     * The 3D Secure object.
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
     * @return string The 3DSecure ACS URL, to send users to
     */
    public function getAcsUrl()
    {
        return $this->acsUrl;
    }

    /**
     * @return string The 3DSecure PA REQ, the token to send along to the ACS URL
     */
    public function getPaReq()
    {
        return $this->paReq;
    }

    /**
     * Get the fields (names and values) to go into the paReq POST.
     * MD = Merchant Data; it is generated by the merchant site, i.e. it is
     * the merchant transaction ID (or an equivalent).
     * $termUrl is the return URL after the PA Request is complete. Add it here,
     * or set it explicitly in your form.
     *
     * @param string|null $termUrl The callback URL, if known at this point
     * @param string|null $md The Merchant Data, if known at this point
     * @return array List of parameter fields and values to go into the PA Req POST
     * @internal param string $merchantData The MD key to identify the transaction in the callback
     */
    public function getPaRequestFields($termUrl = null, $md = null)
    {
        $fields = [
            'PaReq' => $this->getPaReq(),
        ];

        if (isset($termUrl)) {
            $fields['TermUrl'] = $termUrl;
        }

        if (isset($md)) {
            $fields['MD'] = $md;
        }

        return $fields;
    }
}
