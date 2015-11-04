<?php namespace Academe\SagePayMsg\Message;

/**
 * Shared message abstract.
 * Contains base methods that request messages will use.
 */

use Exception;
use UnexpectedValueException;

use DateTime;
use DateTimeZone;

abstract class AbstractRequest extends AbstractMessage
{
    protected $resource_path = [];

    /**
     * @var string Most messages are sent as POST requests, so this is the default
     */
    protected $method = 'POST';

    /**
     * @returns array The path of this resource, as an array of path segments
     */
    public function getResourcePath()
    {
        return $this->resource_path;
    }

    /**
     * @returns string The full URL of this resource
     */
    public function getUrl()
    {
        return $this->auth->getUrl($this->getResourcePath());
    }

    /**
     * @returns string The HTTP method that the 
     */
    public function getMethod()
    {
        return $this->method;
    }
}
