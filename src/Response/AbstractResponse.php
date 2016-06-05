<?php namespace Academe\SagePay\Psr7\Response;

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
     * @return integer The HTTP status code for the response.
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * @param integer $code The HTTP status code for the response.
     */
    protected function setHttpCode($code)
    {
        if (isset($code)) {
            $this->httpCode = (int) $code;
        } else {
            $this->httpCode = null;
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
     * Extract the http response code from the supplied data or the code provied.
     * @param $httpCode
     * @param null $data
     * @return int|null The HTTP code as an integer.
     */
    protected function deriveHttpCode($httpCode, $data = null)
    {
        if (isset($httpCode)) {
            return (int)$httpCode;
        }

        if (isset($data)) {
            $code = Helper::dataGet($data, 'httpCode');

            if (isset($code)) {
                return (int)$code;
            }
        }

        return null;
    }

    /**
     * Handy serialisation.
     * Will be overridden in most responses, then this default can be removed from here.
     */
    public function jsonSerialize()
    {
        return [];
    }

    /**
     * Return an instantiation from the data returned by Sage Pay.
     * TODO: make setData() an abstract method.
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
        return $instance->setData($data, $httpCode);
    }
}
