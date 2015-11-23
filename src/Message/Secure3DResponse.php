<?php namespace Academe\SagePayMsg\Message;

/**
 * The 3DSecure response embedded within a Sage Pay transaction
 * or in response to a Secure3DRequest message.
 * It only includes the status, which gives tje final 3D Secure
 * result for the transaction.
 */

use Exception;
use UnexpectedValueException;

use Academe\SagePayMsg\Helper;

class Secure3DResponse extends AbstractResponse
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
     * @param string $status The status of the 3DSecure result
     */
    public function __construct($status, $httpCode = null)
    {
        $this->status = $status;
        $this->setHttpCode($httpCode);
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
     */
    public static function fromData($data, $httpCode = null)
    {
        return new static(
            Helper::structureGet($data, '3DSecure.status', Helper::structureGet($data, 'status', null)),
            $httpCode
        );
    }

    /**
     * @return string The status of the 3DSecure result
     */
    public function getStatus()
    {
        return $this->status;
    }
}
