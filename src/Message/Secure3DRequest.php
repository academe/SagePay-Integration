<?php namespace Academe\SagePayMsg\Message;

/**
 * The 3DSecure request sent to Sage Pay .
 */

use Exception;
use UnexpectedValueException;

use Academe\SagePayMsg\Helper;
use Academe\SagePayMsg\Model\Auth;

class Secure3DRequest extends AbstractRequest
{
    protected $auth;
    protected $paRes;
    protected $transactionId;

    protected $resource_path = ['transactions', '{transactionId}', '3d-secure'];

    /**
     * @param string|Secure3DAcsResponse $paRes The PA Result returned by the user's bank (or their agent)
     * @param string $transactionId The ID that Sage Pay gave to the transaction in its intial reponse
     */
    public function __construct(Auth $auth, $paRes, $transactionId)
    {
        if ($paRes instanceof Secure3DAcsResponse) {
            $this->paRes = $paRes->getPaRes();
        } else {
            $this->paRes = $paRes;
        }

        $this->transactionId = $transactionId;
        $this->auth = $auth;
    }

    public function getBody()
    {
        return [
            'paRes' => $this->getPaRes(),
        ];
    }

    /**
     * The HTTP Basic Auth header, as an array.
     * Use this if your transport tool does not do "Basic Auth" out of the box.
     */
    public function getHeaders()
    {
        return $this->getBasicAuthHeaders();
    }

    public function getPaRes()
    {
        return $this->paRes;
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }

    public function getAuth()
    {
        return $this->auth;
    }
}
