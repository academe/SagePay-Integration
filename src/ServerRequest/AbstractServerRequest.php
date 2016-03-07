<?php

namespace Academe\SagePay\Psr7\ServerRequest;

use Academe\SagePay\Psr7\Request\AbstractRequest;

abstract class AbstractServerRequest extends AbstractRequest
{
    public static function fromData($data)
    {
        $instance = new static();
        return $instance->setData($data);
    }
}
