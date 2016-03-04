<?php namespace Academe\SagePay\Psr7\Response;

/**
 * The 3DSecure response embedded within a Sage Pay transaction
 * or in response to a Secure3DRequest message.
 * It only includes the status, which gives tje final 3D Secure
 * result for the transaction.
 */

use Exception;
use UnexpectedValueException;
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
     * TODO: $data can be a PSR-7 response.
     * @param array|object $data The 3DSecure resource from Sage Pay
     */
    public function __construct($data, $httpCode = null)
    {
        // If $data is a PSR-7 message, then extract what we need.
        if ($data instanceof ResponseInterface) {
            $data = $this->extractPsr7($data, $httpCode);
        } else {
            $this->setHttpCode($this->deriveHttpCode($httpCode, $data));
        }
        $this->status = Helper::structureGet($data, 'status', null);
    }

    /**
     * Create a new instance from an array or object of values.
     * The data will normally be the whole transaction response with various items
     * of data at different levels, or a flat array.
     * This is possibly misleading, because if there is no 3DSecure data returned
     * at all in the response, then the overall transaction status will be picked
     * up here.
     */

    /**
     * @return string The status of the 3DSecure result
     */
    public function getStatus()
    {
        return $this->status;
    }
}
