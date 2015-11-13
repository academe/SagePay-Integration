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

class TransactionResponse extends AbstractResponse
{
    protected $transactionId;
    protected $trasactionType;
    protected $status;
    protected $statusCode;
    protected $statusDetail;
    protected $retrievalReference;
    protected $bankResponseCode;
    protected $bankAuthorisationCode;

    protected $Secure3D;
    protected $acsUrl;
    protected $paReq;

    protected $transactionTypes = [
        'Payment',
    ];

    protected $statuses = [
        'ok' => 'Ok',
        'notauthed' => 'NotAuthed',
        'rejected' => 'Rejected',
        '3dauth' => '3DAuth',
        'malformed' => 'Malformed',
        'invalid' => 'Invalid',
        'error' => 'Error',
    ];

    /**
     * Big long list of parameters is beginning to smell a bit.
     * We would normally instantiate using fromData() but allowing this
     * constructor to accept a single array or object (simply passing it to
     * fromData() would make it a little less cumbersome. Now we have the
     * 3DSecure object being passed in, rather than just strings and numbers,
     * it changes things a little.
     */
    public function __construct(
        $transactionId,
        $trasactionType,
        $status,
        $statusCode,
        $statusDetail,
        $retrievalReference,
        $bankResponseCode = null,
        $bankAuthorisationCode = null,
        Secure3DResponse $Secure3D = null,
        $acsUrl = null,
        $paReq = null
    ) {
        $this->transactionId = $transactionId;
        $this->trasactionType = $trasactionType;
        $this->status = $status;
        $this->statusCode = $statusCode;
        $this->statusDetail = $statusDetail;
        $this->retrievalReference = $retrievalReference;
        $this->bankResponseCode = $bankResponseCode;
        $this->bankAuthorisationCode = $bankAuthorisationCode;
        $this->Secure3D = $Secure3D;
        $this->acsUrl = $acsUrl;
        $this->paReq = $paReq;
    }

    public static function fromData($data, $httpCode = null)
    {
        // Note the object is called "3DSecure" and not "Secure3D" that use
        // for valid class, method and variable names.
        $Secure3D = Helper::structureGet($data, '3DSecure', null);

        if ($Secure3D instanceof Secure3DResponse) {
            // A 3DSecure object has already been put together.
        } elseif (is_array($Secure3D)) {
            // Create a 3DSecure object from the array data, but include 
            $Secure3D = Secure3DResponse::fromData($data);
        } elseif (is_null($Secure3D)) {
            // No 3D Secure object; the 3D Secure part of the transactino is
            // not yet complete.
        } else {
            // Don't know how to handle this data.
            // TODO: Exception.
        }

        $response = new static(
            Helper::structureGet($data, 'transactionId', null),
            Helper::structureGet($data, 'trasactionType', null),
            Helper::structureGet($data, 'status', null),
            Helper::structureGet($data, 'statusCode', null),
            Helper::structureGet($data, 'statusDetail', null),
            Helper::structureGet($data, 'retrievalReference', null),
            Helper::structureGet($data, 'bankResponseCode', null),
            Helper::structureGet($data, 'bankAuthorisationCode', null),
            // 3D Secure details.
            $Secure3D,
            Helper::structureGet($data, 'acsUrl', null),
            Helper::structureGet($data, 'paReq', null)
        );

        // We can access the protected method here, because $response is an
        // instantiation of stetic.
        $response->storeHttpCode($response, $data, $httpCode);

        return $response;
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

        return ! empty($this->statuses[strtolower($this->status)])
            ? $this->statuses[strtolower($this->status)]
            : $this->status;
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
     * @param string $merchantData The MD key to identify the transaction in the callback
     * @param string|null $termUrl The callback URL, if known at this point
     * @param string|null $md The Merchant Data, if known at this point
     *
     * @return array List of parameter fields and values to go into the PA Req POST
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
