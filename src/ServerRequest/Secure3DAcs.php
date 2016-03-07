<?php namespace Academe\SagePay\Psr7\ServerRequest;

/**
 * The ACS POST response that the issuing bank’s Access Control System (ACS)
 * or their agent sends the user back with.
 * This will include the optional MD for finding the transaction again, and the hashed
 * PaRes result that is then sent to Sage Pay to complete the transaction.
 */

use Academe\SagePay\Psr7\Helper;
use Academe\SagePay\Psr7\ServerRequest\AbstractServerRequest;
use Psr\Http\Message\ServerRequestInterface;

class Secure3DAcs extends AbstractServerRequest
{
    protected $PaRes;
    protected $MD;

    /**
     * @param $message ServerRequestInterface The 3DSecure resource callback from Sage Pay.
     */
    public function __construct(ServerRequestInterface $message = null) {
        if (isset($message)) {
            $this->setData($this->parseBody($message));
        }
    }

    /**
     * @param $data array|object The 3DSecure resource callback from Sage Pay.
     * @return $this
     */
    protected function setData($data)
    {
        $this->PaRes = Helper::structureGet($data, 'PaRes', null);
        $this->MD = Helper::structureGet($data, 'MD', null);
        return $this;
    }

    public function jsonSerialize()
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
