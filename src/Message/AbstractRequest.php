<?php namespace Academe\SagePay\Message;

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
     * The path of this resource.
     */
    public function getResourcePath()
    {
        return $this->resource_path;
    }

    /**
     * The full URL of this resource.
     */
    public function getUrl()
    {
        return $this->auth->getUrl($this->getResourcePath());
    }
}
