<?php namespace Academe\SagePayMsg\Message;

/**
 * Shared message abstract.
 */

use Exception;
use UnexpectedValueException;

use Teapot\StatusCode\RFC\RFC2616;
use Teapot\StatusCode\RFC\RFC2324;
use Teapot\StatusCode\RFC\RFC2774;

abstract class AbstractResponse extends AbstractMessage implements RFC2616, RFC2324, RFC2774
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
}
