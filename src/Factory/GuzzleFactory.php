<?php namespace Academe\SagePay\Psr7\Factory;
/**
 * Guzzle Factory for creating PSR-7 objects.
 * Requires guzzlehttp/guzzle:~6.0
 * TODO: a method to check if Guzzle is installed, indicating that this
 * factory can be used.
 */

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use GuzzleHttp\Psr7\Request;

class GuzzleFactory implements FactoryInterface
{
    /**
     * Return a new GuzzleHttp\Psr7\Request object.
     * The body is to be sent as a JSON request.
     */
    public function JsonRequest($method, $uri, array $headers = [], $body = null, $protocolVersion = '1.1')
    {
        // If we are sending a JSON body, then the recipient needs to know.
        $headers['Content-type'] = 'application/json';

        // If the body is already a stream or string of some sort, then it is
        // assumed to already be a JSON stream.
        if ( ! is_string($body) && ! $body instanceof StreamInterface && gettype($body) != 'resource') {
            $body = json_encode($body);
        }

        return new Request(
            $method,
            $uri,
            $headers,
            $body,
            $protocolVersion
        );
    }

    /**
     * Check whether Guzzle is installed so this factory can be used.
     */
    public static function isSupported()
    {
        return class_exists(Request::class);
    }
}
