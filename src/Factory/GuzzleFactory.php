<?php

namespace Academe\SagePay\Psr7\Factory;

/**
 * Guzzle Factory for creating PSR-7 objects.
 * Requires guzzlehttp/guzzle:~6.0
 */

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

class GuzzleFactory implements RequestFactoryInterface
{
    /**
     * Return a new GuzzleHttp\Psr7\Request object.
     * The body is to be sent as a JSON request.
     * @param null|string $method
     * @param UriInterface|null|string $uri
     * @param array $headers
     * @param null $body
     * @param string $protocolVersion
     * @return Request
     */
    public function jsonRequest($method, $uri, array $headers = [], $body = null, $protocolVersion = '1.1')
    {
        // If we are sending a JSON body, then the recipient needs to know.
        $headers['Content-type'] = 'application/json';

        // If the body is already a stream or string of some sort, then it is
        // assumed to already be a JSON stream.
        if (! is_string($body) && ! $body instanceof StreamInterface && gettype($body) != 'resource') {
            $body = json_encode($body);
        }

        // Guzzle will accept the body as a string and generate a stream from it.
        return new Request(
            $method,
            $uri,
            $headers,
            $body,
            $protocolVersion
        );
    }

    /**
     * Create a PSR-7 UriInterface object.
     * Experimental.
     * @param string $uri
     * @return Uri
     */
    public function uri($uri)
    {
        return new Uri($uri);
    }

    /**
     * Check whether Guzzle PSR-7 is installed so this factory can be used.
     * Note: Guzzle does not support everything (e.g. not ServerRequestInterface at this time).
     * @return bool
     */
    public static function isSupported()
    {
        return class_exists(Request::class);
    }
}
