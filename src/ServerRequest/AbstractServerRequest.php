<?php

namespace Academe\Opayo\Pi\ServerRequest;

/**
 * TODO: implement parseBody() here to check getParsedBody() before falling
 * back to parent::parseBody() if not set.
 */

use Academe\Opayo\Pi\Request\AbstractRequest;

abstract class AbstractServerRequest extends AbstractRequest
{
    /**
     * @param $data
     * @return mixed
     */
    public static function fromData($data)
    {
        $instance = new static();
        return $instance->setData($data);
    }

    /**
     * @param $data
     */
    abstract protected function setData($data);
}
