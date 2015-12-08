<?php namespace Academe\SagePayMsg\Message;

/**
 * Shared message abstract.
 */

use Exception;
use UnexpectedValueException;

use Academe\SagePayMsg\Helper;

// Teapot here provides HTTP response code constants.
// Not sure why RFC4918 is not included in Http; it contains some responses we expect to get.
use Teapot\StatusCode\Http;
use Teapot\StatusCode\RFC\RFC4918;

abstract class AbstractResponse extends AbstractMessage implements Http, RFC4918
{
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
     * @return integer|null The HTTP code as an integer.
     */
    protected function deriveHttpCode($httpCode, $data = null)
    {
        if (isset($httpCode)) {
            return (int)$httpCode;
        }

        if (isset($data)) {
            $code = Helper::structureGet($data, 'httpCode');

            if (isset($code)) {
                return (int)$code;
            }
        }
    }
}
