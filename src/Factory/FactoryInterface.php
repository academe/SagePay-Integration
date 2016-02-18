<?php namespace Academe\SagePay\Psr7\Factory;

/**
 * Factory interface for creating PSR-7 objects.
 * The implementation will often be Guzzle (GuzzleFactory), but the interface
 * allows other implementations to be used.
 */

interface FactoryInterface
{
    /**
     * Return a new PSR-7 Request object, with the body to be sent as JSON.
     */
    public function JsonRequest(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    );
}
