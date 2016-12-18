<?php

namespace Academe\SagePay\Psr7\Response;

/**
 * Shared message abstract.
 */

use JsonSerializable;
use Academe\SagePay\Psr7\Helper;
use Academe\SagePay\Psr7\AbstractMessage;
use Psr\Http\Message\ResponseInterface;

// Teapot here provides HTTP response code constants.
// Not sure why RFC4918 is not included in Http; it contains some responses we expect to get.
use Teapot\StatusCode\Http;
use Teapot\StatusCode\RFC\RFC4918;

abstract class AbstractResponse extends AbstractMessage implements Http, RFC4918, JsonSerializable
{
    /**
     * Transaction status from Sage Pay.
     */
    const STATUS_OK         = 'Ok';
    const STATUS_NOTAUTHED  = 'NotAuthed';
    const STATUS_REJECTED   = 'Rejected';
    const STATUS_3DAUTH     = '3DAuth';
    const STATUS_MALFORMED  = 'Malformed';
    const STATUS_INVALID    = 'Invalid';
    const STATUS_ERROR      = 'Error';

    /**
     * @var integer The HTTP response code.
     */
    protected $httpCode;

    /**
     * The status, statusCode and statusReason are used in most response messages.
     */
    protected $status;
    protected $statusCode;
    protected $statusDetail;

    /**
     * This constructor and fromData() are the two instantation points of this class,
     * and are the only two places where the httpStatus is set, and the Sage Pay statuses
     * are set.
     *
     * @param ResponseInterface $message
     * @internal param array|object|ResponseInterface $data
     */
    public function __construct(ResponseInterface $message = null)
    {
        if (isset($message)) {
            $this->setHttpCode($message->getStatusCode());
            $data = $this->parseBody($message);
            $this->setData($data);
            $this->setStatuses($data);
        }
    }

    /**
     * @return integer The HTTP status code for the response.
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * Set the httpCode only if not null.
     * @param integer|null $code The HTTP status code for the response
     */
    protected function setHttpCode($code)
    {
        if (isset($code)) {
            $this->httpCode = (int) $code;
        }
    }

    /**
     * @param integer $code The HTTP status code for the response.
     *
     * @return self Clone of $this with the HTTP code set.
     */
    public function withHttpCode($code)
    {
        $clone = clone $this;
        $clone->setHttpCode($code);
        return $clone;
    }

    /**
     * Set the usual three status fields from body data.
     * @param array $data The response message body data.
     * @return null
     */
    protected function setStatuses($data)
    {
        $this->status       = Helper::dataGet($data, 'status', null);
        $this->statusCode   = Helper::dataGet($data, 'statusCode', null);
        $this->statusDetail = Helper::dataGet($data, 'statusDetail', null);
    }

    /**
     * There is a status (e.g. Ok), a statusCode (e.g. 2007), and a statusDetail (e.g. Transaction authorised).
     * Also there is a HTTP return code (e.g. 202). All are needed in different contexts.
     * However, there is a hint that the "status" may be removed, relying on the HTTP return code instead.
     * @return string The overall status string of the transaction.
     */
    public function getStatus()
    {
        // Enforce the correct capitalisation.

        $statusValue = $this->constantValue('STATUS', $this->status);

        return ! empty($statusValue) ? $statusValue : $this->status;
    }

    /**
     * @return string The numeric code that represents the status detail.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * This message in some range of codes can be presented to the end user.
     * In other ranges of codes it should only ever be logged fot the site administrator.
     * @return string The detailed status message.
     */
    public function getStatusDetail()
    {
        return $this->statusDetail;
    }

    /**
     * Set properties from an array or object of values.
     * This response will be returned either embedded into a Payment (if 3DSecure is not
     * enabled, or a Payment is being fetched from storage) or on its own in response to
     * sending the paRes to Sage Pay.
     *
     * @param $data
     * @return $this
     */
    protected abstract function setData($data);

    /**
     * Return an instantiation from the body data returned by Sage Pay.
     *
     * @param string|array|object $data
     * @param null $httpCode
     * @return
     */
    public static function fromData($data, $httpCode = null)
    {
        // If a string, then assume it is JSON.
        // This way the session can be JSON serialised for passing between pages.
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        $instance = new static();

        $instance->setHttpCode($httpCode);
        $instance->setData($data);
        $instance->setStatuses($data);

        return $instance;
    }

    /**
     * Determine whether the response data looks like this kind of message.
     *
     * @param array|object $data Response message body data.
     * @return boolean True if the data matches this kind of response.
     */
    public static function isResponse($data)
    {
        return false;
    }

    /**
     * Indicate whether the response is an error or not.
     * @return boolean True if the response is an error collection.
     */
    public function isError()
    {
        return false;
    }

    /**
     * Indicate whether the response is a 3D Secure redirect.
     * @return boolean True if the response is a Secure3DRedirect.
     */
    public function isRedirect()
    {
        return false;
    }

    /**
     * Indicate whether the authorisation or 3D Secure password was successful.
     * @return boolean True if the response is a successful (in context) transaction result.
     */
    public function isSuccess()
    {
        return false;
    }

    /**
     * Convenient serialisation for logging and debugging.
     * Each response message would extend this where appropriate.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $return = [
            'httpCode' => $this->getHttpCode(),
            'status' => $this->getStatus(),
            'statusCode' => $this->getStatusCode(),
            'statusDetail' => $this->getStatusDetail(),
        ];

        return $return;
    }
}
