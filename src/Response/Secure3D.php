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
     * TODO: better less-generic prefix needed.
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
     * This response will be returned either embedded into a Payment (if 3DSecure is not
     * enabled, or a Payment is being fetched from storage) or on its own in response to
     * sending the paRes to Sage Pay.
     *
     * @param $data
     * @param null|string $httpCode
     * @return $this
     */
    protected function setData($data, $httpCode = null)
    {
        $this->setHttpCode($this->deriveHttpCode($httpCode, $data));
        $this->status = Helper::dataGet($data, '3DSecure.status', null);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function isResponse(array $data)
    {
        return !empty(Helper::dataGet($data, '3DSecure.status'));
    }

    /**
     * @inheritdoc
     * CHECKME: any other statuses considered sucessful?
     */
    public function isSuccess()
    {
        return $this->getStatus() == static::STATUS_AUTHENTICATED;
    }
}
