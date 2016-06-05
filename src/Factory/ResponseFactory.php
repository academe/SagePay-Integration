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
     */
    public static function parse(ResponseInterface $response)
    {
        // Get the overall HTTP status.
        $http_code = $response->getStatusCode();
        $http_reason = $response->getReasonPhrase();

        // Decoding the body, as that is where all the details will be.
        $data = Helper::parseBody($response);

        // A HTTP error code.
        // Some errors may come from Sage Pay. Some may involve not being
        // able to contact Sage Pay at all.
        if ($http_code >= 400 && $http_code < 500) {
            // 4xx errors.
            // Return an error collection.
            return new Response\ErrorCollection($response);
        }

        // A card identifier message.
        if (Helper::dataGet($data, 'cardIdentifier')) {
            return new Response\CardIdentifier($response);
        }

        // A payment.
        if (Helper::dataGet($data, 'transactionId') && Helper::dataGet($data, 'transactionType') == AbstractRequest::TRANSACTION_TYPE_PAYMENT) {
            return new Response\Payment($response);
        }

        // A repeat payment.
        if (Helper::dataGet($data, 'transactionId') && Helper::dataGet($data, 'transactionType') == AbstractRequest::TRANSACTION_TYPE_REPEAT) {
            return new Response\Repeat($response);
        }

        // Session key
        if (Helper::dataGet($data, 'merchantSessionKey') && Helper::dataGet($data, 'expiry')) {
            return new Response\SessionKey($response);
        }

        // 3D Secure response.
        if (Helper::dataGet($data, '3DSecure.status')) {
            return new Response\Secure3D($response);
        }

        // A 3D Secure redirect is required.
        if (Helper::dataGet($data, 'statusCode') == '2007' && Helper::dataGet($data, 'status') == AbstractResponse::STATUS_3DAUTH) {
            return new Response\Secure3DRedirect($response);
        }

        return $data;
    }
}
