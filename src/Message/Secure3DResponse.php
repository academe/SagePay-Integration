<?php namespace Academe\SagePayMsg\Message;

/**
 * The POST response that the issuing bank’s Access Control System (ACS)
 * sends the user back with.
 * This will include the MD for finding the transaction again, and the hashed
 * PaRes result that is then sent to Sage Pay to complete the transaction.
 */

use Exception;
use UnexpectedValueException;

use Academe\SagePayMsg\Helper;

class Secure3DResponse extends AbstractMessage
{
    protected $MD;
    protected $PaRes;

    public function __construct(
        $MD,
        $PaRes
    ) {
        $this->MD = $MD;
        $this->PaRes = $PaRes;
    }

    /**
     * Data here will be the raw POST array.
     */
    public static function fromData($data)
    {
        return new static(
            Helper::structureGet($data, 'MD', null),
            Helper::structureGet($data, 'PaRes', null)
        );
    }

    /**
     * The Merchant Data (MD) to identify the transaction.
     */
    public function getMD()
    {
        return $this->MD;
    }

    /**
     * The hashed 3DSecure result (PaRes) to pass on to Sage Pay for validation.
     */
    public function getPaRes()
    {
        return $this->PaRes;
    }
}
