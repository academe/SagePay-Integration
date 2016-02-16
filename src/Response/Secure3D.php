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

class Secure3D extends AbstractResponse
{
    /**
     * The acsUrl and paReq should NEVER be stored in the database.
     * @var
     */
    protected $status;

    /**
     * @var array List of statuses that the 3DSecure object can return
     */
    protected $statuses = [
        'authenticated' => 'Authenticated',
        'force' => 'Force',
        'notchecked' => 'NotChecked',
        'notauthenticated' => 'NotAuthenticated',
        'error' => 'Error',
        'cardnotenrolled' => 'CardNotEnrolled',
        'issuernotenrolled' => 'IssuerNotEnrolled',
    ];

    /**
     * @param array|object $data The 3DSecure resource from Sage Pay
     */
    public function __construct($data, $httpCode = null)
    {
        $this->status = Helper::structureGet($data, 'status', null);

        $this->setHttpCode($this->deriveHttpCode($httpCode, $data));
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
     * @param $data Array of single-level data or raw transaction response to initialise the object
     *
     * @return static New instance of Secure3D object
     * @deprecated
     */
    public static function fromData($data, $httpCode = null)
    {
        return new static ($data, $httpCode);
    }

    /**
     * @return string The status of the 3DSecure result
     */
    public function getStatus()
    {
        return $this->status;
    }
}
