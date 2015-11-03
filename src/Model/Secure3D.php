<?php namespace Academe\SagePayMsg\Model;

/**
 * The 3DSecure response from a Sage Pay transaction.
 * It only includes the status for now, and the acsUrl and paReq
 * are bizarrely not in this object. Maybe we should put those here
 * too, as optional properties, i.e. put ALL 3DSecure data into
 * this one object?
 */

use Exception;
use UnexpectedValueException;

use Academe\SagePayMsg\Helper;

class Secure3D
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
     * @param string|null $acsUrl The ACS URL of the 3DSecure result
     * @param string|null $paReq The PA Res of the 3DSecure result
     */
    public function __construct($status)
    {
        $this->status = $status;
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
    public static function fromData($data)
    {
        return new static(
            Helper::structureGet($data, '3DSecure.status', Helper::structureGet($data, 'status', null))
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
