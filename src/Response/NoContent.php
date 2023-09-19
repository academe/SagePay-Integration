<?php

namespace Academe\SagePay\Psr7\Response;

/**
 * A simple (success) response with no data.
 */

use Psr\Http\Message\ResponseInterface;

class NoContent extends AbstractResponse
{
    /**
     * No data to set (this is an empty messgae body).
     */
    public function setData($data)
    {
    }

    /**
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [];
    }
}
