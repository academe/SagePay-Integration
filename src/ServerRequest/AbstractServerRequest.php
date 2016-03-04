<?php

namespace Academe\SagePay\Psr7\ServerRequest;

use Academe\SagePay\Psr7\Request\AbstractRequest;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractServerRequest extends AbstractRequest
{
    /**
     * Extract the body and HTTP code from a PSR-7 ServerRequestInterface message.
     */
    protected function extractPsr7(ServerRequestInterface $message)
    {
        $data = [];

        if ($message->hasHeader('Content-Type')) {
            if ($message->getHeaderLine('Content-Type') === 'application/x-www-form-urlencoded') {
                parse_str((string)$message->getBody(), $data);
            } elseif ($message->getHeaderLine('Content-Type') === 'application/json') {
                // Sage Pay does not send the request as JSON, yet, but be ready just in case.
                $data = json_decode($message->getBody());
            }
        }

        return $data;
    }
}
