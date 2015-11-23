<?php namespace Academe\SagePayMsg\Message;

/**
 * The ACS POST response that the issuing bank’s Access Control System (ACS)
 * or their agent sends the user back with.
 * This will include the optional MD for finding the transaction again, and the hashed
 * PaRes result that is then sent to Sage Pay to complete the transaction.
 */

use Exception;
use UnexpectedValueException;

use Academe\SagePayMsg\Helper;

class Secure3DAcsResponse extends AbstractResponse
{
    protected $PaRes;
    protected $MD;

    public function __construct($data, $httpCode = null) {
        $this->PaRes = Helper::structureGet($data, 'PaRes', null);
        $this->MD = Helper::structureGet($data, 'MD', null);

        $this->setHttpCode($this->deriveHttpCode($httpCode, $data));
    }

    /**
     * Data passed in here will normally be the raw POST array from the ACS.
     *
     * @deprecated
     */
    public static function fromData($data, $httpCode = null)
    {
        return new static($data, $httpCode);
    }

    public function asArray()
    {
        return [
            'PaRes' => $this->getPaRes(),
            'MD' => $this->getMD(),
        ];
    }

    /**
     * @returns string The optional Merchant Data (MD) to identify the transaction.
     */
    public function getMD()
    {
        return $this->MD;
    }

    /**
     * @returns string The encrypted 3DSecure result (PaRes) to pass on to Sage Pay for validation.
     */
    public function getPaRes()
    {
        return $this->PaRes;
    }
}
