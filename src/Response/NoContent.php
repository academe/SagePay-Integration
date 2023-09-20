<?php

namespace Academe\SagePay\Psr7\Response;

/**
 * A simple (success) response with no data.
 */

use Psr\Http\Message\ResponseInterface;

class NoContent extends AbstractResponse
{
    /**
     * No data to set (this is an empty message body).
     * @inheritDoc
     */
    public function setData($data)
    {
        return $this;
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
