<?php

namespace Academe\SagePay\Psr7\Factory;

/**
 * Factory to return the appropriate Response object given
 * the PSR-7 HTTP Response object. This handles a lot of logic,
 * such as checking for errors in a number of different places,
 * and knowing exactly which Response object to create, that the
 * application would otherwise have to deal with.
 */

use Psr\Http\Message\ResponseInterface;
use Academe\SagePay\Psr7\Helper;
use Academe\SagePay\Psr7\Response;
use Academe\SagePay\Psr7\Request\AbstractRequest;
use Academe\SagePay\Psr7\Response\AbstractResponse;

class ResponseFactory
{
    /**
     * Parse a PSR-7 Response message.
     * TODO: handle 500+ errors.
     * TODO: as a response can contain multiple messages combined into one (e.g. a payment method,
     * a payment result and a 3D Secure result) then a method would be helpful to list all the
     * responses that have been detected, then parse can take a parameter to limit its parsing
     * to just one of those messages if needed. However, parsing of some messages (e.g. Payment)
     * will recursively parse out other messages it contains anyway. It is almost like they
     * shoul be namespaced, since they are all overlapping.
     */
    public static function parse($response)
    {
        if ($response instanceof ResponseInterface) {
            // Decoding the body, as that is where all the details will be.
            $data = Helper::parseBody($response);

            // Get the overall HTTP status.
            $http_code = $response->getStatusCode();
            $http_reason = $response->getReasonPhrase();
        } else {
            $data = $response;
            $http_code = 200;
            $http_reason = null;
        }

        // A HTTP error code.
        // Some errors may come from Sage Pay. Some may involve not being
        // able to contact Sage Pay at all.
        if ($http_code >= 400 || Response\ErrorCollection::isResponse($data)) {
            // 4xx and 5xx errors.
            // Return an error collection.
            if ($response instanceof ResponseInterface) {
                return new Response\ErrorCollection($response);
            } else {
                return Response\ErrorCollection::fromData($data, $http_code);
            }
        }

        // A card identifier message.
        if (Response\CardIdentifier::isResponse($data)) {
            if ($response instanceof ResponseInterface) {
                return new Response\CardIdentifier($response);
            } else {
                return Response\CardIdentifier::fromData($data, $http_code);
            }
        }

        // A payment.
        if (Response\Payment::isResponse($data)) {
            return new Response\Payment($response);
        }

        // A repeat payment.
        if (Response\Repeat::isResponse($data)) {
            return new Response\Repeat($response);
        }

        // Session key
        if (Response\SessionKey::isResponse($data)) {
            return new Response\SessionKey($response);
        }

        // 3D Secure response.
        // This is a 3D Secure response *on its own*. They also appear
        // embedded in a Payment, and so need to be pulled out separately
        // from there.
        if (Response\Secure3D::isResponse($data)) {
            return new Response\Secure3D($response);
        }

        // PaymentMethod response.
        // This is a PaymentMethod response *on its own*. They also appear
        // embedded in a Payment, and so need to be pulled out separately
        // from there.
        if (Response\PaymentMethod::isResponse($data)) {
            return new Response\PaymentMethod($response);
        }

        // A 3D Secure redirect is required.
        if (Response\Secure3DRedirect::isResponse($data)) {
            return new Response\Secure3DRedirect($response);
        }

        return $data;
    }
}
