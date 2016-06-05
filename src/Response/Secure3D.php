<?php namespace Academe\SagePay\Psr7\Response;

/**
 * The 3DSecure response embedded within a Sage Pay transaction
 * or in response to a Secure3DRequest message.
 * It only includes the status, which gives tje final 3D Secure
 * result for the transaction.
 */

use Academe\SagePay\Psr7\Helper;
use Psr\Http\Message\ResponseInterface;

class Secure3D extends AbstractResponse
{
    /**
     * The acsUrl and paReq should NEVER be stored in the database.
     * @var
     */
    protected $status;

    /**
     * List of statuses that the 3DSecure object can return
     */
    const STATUS_AUTHENTICATED = 'Authenticated';
    const STATUS_FORCE = 'Force';
    const STATUS_NOTCHECKED = 'NotChecked';
    const STATUS_NOTAUTHENTICATED = 'NotAuthenticated';
    const STATUS_ERROR = 'Error';
    const STATUS_CARDNOTENROLLED = 'CardNotEnrolled';
    const STATUS_ISSUERNOTENROLLED = 'IssuerNotEnrolled';

    /**
     * @param ResponseInterface $message
     * @internal param array|object $data The 3DSecure resource from Sage Pay
     */
    public function __construct(ResponseInterface $message = null)
    {
        if (isset($message)) {
            $data = $this->parseBody($message);
            $this->setData($data, $message->getStatusCode());
        }
    }

    /**
     * Set properties from an array or object of values.
     * CHECKME: should this be 3DSecure.status?
     *
     * @param $data
     * @param null|string $httpCode
     * @return $this
     */
    protected function setData($data, $httpCode = null)
    {
        $this->setHttpCode($this->deriveHttpCode($httpCode, $data));
        $this->status = Helper::dataGet($data, 'status', null);
        return $this;
    }

    /**
     * @return string The status of the 3DSecure result
     */
    public function getStatus()
    {
        return $this->status;
    }
}
