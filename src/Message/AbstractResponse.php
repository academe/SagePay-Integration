<?php namespace Academe\SagePayMsg\Message;

/**
 * Shared message abstract.
 */

use Exception;
use UnexpectedValueException;

use Academe\SagePayMsg\Helper;

// Teapot here provides HTTP response code constants.
use Teapot\StatusCode\Http;

abstract class AbstractResponse extends AbstractMessage implements Http
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
     * Store the HTTP response code passed in through fromData().
     * Take the explicit code or take it from the data array.
     */
    protected function storeHttpCode(AbstractResponse $response, $data, $httpCode = null)
    {
        if (isset($httpCode)) {
            // The httpCode has been explicitly passed in.
            $response->setHttpCode($httpCode);
        } else {
            // The httpCode can be pushed onto the data object/array for convenience.
            $response->setHttpCode(Helper::structureGet($data, 'httpCode', null));
        }
    }
}
