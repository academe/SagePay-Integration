<?php

namespace Academe\SagePay\Psr7\Factory;

/**
 * Factory to return the appropriate Response object given
 * the PSR-7 HTTP Response object. This handles a lot of logic,
 * such as checking for errors in a number of different places,
 * and knowing exactly which Response object to create, that the
 * application would otherwise have to deal with.
 */

use Academe\SagePay\Psr7\Response\AbstractTransaction;
use Academe\SagePay\Psr7\Request\AbstractRequest;
use Psr\Http\Message\ResponseInterface;
use Academe\SagePay\Psr7\Response;
use Academe\SagePay\Psr7\Helper;
use Teapot\StatusCode\Http;

class ResponseFactory
{
    /**
     * Return a response instance from a PSR-7 Response message.
     */
    public static function fromHttpResponse(ResponseInterface $response)
    {
        // Decode the body for the returned data.
        $data = Helper::parseBody($response);

        // Get the HTTP status.
        $httpCode = $response->getStatusCode();
        $httpReason = $response->getReasonPhrase();

        // Return the response object.
        return static::fromData($data, $httpCode);
    }

    /**
     * Return a response instance from response data.
     */
    public static function fromData($data, $httpCode = null)
    {
        // An error or error collection.
        if ($httpCode >= Http::BAD_REQUEST || Helper::dataGet($data, 'errors')) {
            // 4xx and 5xx errors.
            // Return an error collection.
            return Response\ErrorCollection::fromData($data, $httpCode);
        }

        // Session key.
        if (Helper::dataGet($data, 'merchantSessionKey') && Helper::dataGet($data, 'expiry')) {
            return Response\SessionKey::fromData($data, $httpCode);
        }

        // A card identifier.
        if (Helper::dataGet($data, 'cardIdentifier') || Helper::dataGet($data, 'card-identifier')) {
            return Response\CardIdentifier::fromData($data, $httpCode);
        }

        // A payment.
        if (Helper::dataGet($data, 'transactionId')) {
            if (Helper::dataGet($data, 'transactionType') == AbstractRequest::TRANSACTION_TYPE_PAYMENT) {
                return Response\Payment::fromData($data, $httpCode);
            }
        }

        // A repeat payment.
        if (Helper::dataGet($data, 'transactionId')) {
            if (Helper::dataGet($data, 'transactionType') == AbstractRequest::TRANSACTION_TYPE_REPEAT) {
                return Response\Repeat::fromData($data, $httpCode);
            }
        }

        // A refund payment.
        if (Helper::dataGet($data, 'transactionId')) {
            if (Helper::dataGet($data, 'transactionType') == AbstractRequest::TRANSACTION_TYPE_REFUND) {
                return Response\Refund::fromData($data, $httpCode);
            }
        }

        // A deferred payment.
        if (Helper::dataGet($data, 'transactionId')) {
            if (Helper::dataGet($data, 'transactionType') == AbstractRequest::TRANSACTION_TYPE_DEFERRED) {
                return Response\Deferred::fromData($data, $httpCode);
            }
        }

        // 3D Secure response.
        // This is the simplest of all the messages - just a status and nothing else.
        // Make sure there is no transactionType field.

        $secure3dStatusList = Response\Secure3D::constantList('STATUS3D');
        $status = Helper::dataGet($data, 'status');

        if ($status && in_array($status, $secure3dStatusList) && ! Helper::dataGet($data, 'transactionId')) {
            return Response\Secure3D::fromData($data, $httpCode);
        }

        // A 3D Secure redirect.
        // Like Secure3D, this one does not have a TransactionType, though shares many fields
        // with the abstract transaction response.
        if (Helper::dataGet($data, 'statusCode') == '2007') {
            if (Helper::dataGet($data, 'status') == AbstractTransaction::STATUS_3DAUTH) {
                return Response\Secure3DRedirect::fromData($data, $httpCode);
            }
        }

        // A void instruction.
        if (Helper::dataGet($data, 'instructionType') == AbstractRequest::INSTRUCTION_TYPE_VOID) {
            return Response\VoidInstruction::fromData($data, $httpCode);
        }

        // An abort instruction.
        if (Helper::dataGet($data, 'instructionType') == AbstractRequest::INSTRUCTION_TYPE_ABORT) {
            return Response\Abort::fromData($data, $httpCode);
        }

        // A release instruction.
        if (Helper::dataGet($data, 'instructionType') == AbstractRequest::INSTRUCTION_TYPE_RELEASE) {
            return Response\Release::fromData($data, $httpCode);
        }

        // A list of instructions.
        if (Helper::dataGet($data, 'instructions') && is_array(Helper::dataGet($data, 'instructions'))) {
            return Response\InstructionCollection::fromData($data, $httpCode);
        }

        // A 204 with an empty body is a quiet accpetance that what was send is successful.
        // e.g. returned when a CVV is linked to a card.
        if ($httpCode == 204 && empty($data)) {
            return Response\NoContent::fromData($data, $httpCode);
        }
    }
}
