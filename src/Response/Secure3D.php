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
     * List of statuses that the 3DSecure object can return.
     */
    const RESULT3D_AUTHENTICATED = 'Authenticated';
    const RESULT3D_FORCE = 'Force';
    const RESULT3D_NOTCHECKED = 'NotChecked';
    const RESULT3D_NOTAUTHENTICATED = 'NotAuthenticated';
    const RESULT3D_ERROR = 'Error';
    const RESULT3D_CARDNOTENROLLED = 'CardNotEnrolled';
    const RESULT3D_ISSUERNOTENROLLED = 'IssuerNotEnrolled';

    /**
     * The 3D Secure status, which annoyingly has the same name as the overall
     * transaction status in the message data, so we will call it "result" to
     * same some confusion.
     */
    protected $result;

    /**
     * @param $data
     * @return $this
     */
    protected function setData($data)
    {
        $this->result = Helper::dataGet($data, '3DSecure.status', null);
        return $this;
    }

    /**
     * The 3D Secure result.
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @inheritdoc
     */
    public static function isResponse($data)
    {
        return !empty(Helper::dataGet($data, '3DSecure.status'));
    }

    /**
     * @inheritdoc
     * CHECKME: any other statuses considered sucessful?
     */
    public function isSuccess()
    {
        return $this->getStatus() == static::RESULT3D_AUTHENTICATED;
    }

    /**
     * Convenient serialisation for logging and debugging.
     * @return array
     */
    public function jsonSerialize()
    {
        $return = parent::jsonSerialize();

        $return['result'] = $this->getResult();

        return $return;
    }
}
