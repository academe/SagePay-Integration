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
     */
    public static function parse(ResponseInterface $response)
    {
        // Decoding the body, as that is where all the details will be.
        $data = Helper::parseBody($response);

        // Get the overall HTTP status.
        $http_code = $response->getStatusCode();
        $http_reason = $response->getReasonPhrase();

        // A HTTP error code.
        // Some errors may come from Sage Pay. Some may involve not being
        // able to contact Sage Pay at all.
        if ($http_code >= 400 || Response\ErrorCollection::isResponse($data)) {
            // 4xx and 5xx errors.
            // Return an error collection.
            return new Response\ErrorCollection($response);
        }

        // A card identifier message.
        if (Response\CardIdentifier::isResponse($data)) {
            return new Response\CardIdentifier($response);
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
        if (Response\Secure3D::isResponse($data)) {
            return new Response\Secure3D($response);
        }

        // A 3D Secure redirect is required.
        if (Response\Secure3DRedirect::isResponse($data)) {
            return new Response\Secure3DRedirect($response);
        }

        return $data;
    }
}
