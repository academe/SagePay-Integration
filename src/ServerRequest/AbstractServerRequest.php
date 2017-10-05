<?php

namespace Academe\SagePay\Psr7\ServerRequest;

/**
 * TODO: implement parseBody() here to check getParsedBody() before falling
 * back to parent::parseBody() if not set.
 */

use Academe\SagePay\Psr7\Request\AbstractRequest;

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
