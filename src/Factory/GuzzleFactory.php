<?php namespace Academe\SagePay\Psr7\Factory;
/**
 * Guzzle Factory for creating PSR-7 objects.
 * Requires guzzlehttp/guzzle:~6.0
 * TODO: a method to check if Guzzle is installed, indicating that this
 * factory can be used.
 */

use GuzzleHttp\Psr7\Request;

class GuzzleFactory implements FactoryInterface
{
    /**
     * Return a new GuzzleHttp\Psr7\Request object.
     * The body is to be sent as a JSON request.
     * If a string is passed in as the body, then assume it is already JSON.
     */
    public function JsonRequest($method, $uri, array $headers = [], $body = null, $protocolVersion = '1.1')
    {
        // If we are sending a JSON body, then the recipient needs to know.
        $headers['Content-type'] = 'application/json';

        if (!is_string($body)) {
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
