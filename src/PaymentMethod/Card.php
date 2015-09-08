<?php namespace Academe\SagePayMsg\PaymentMethod;

/**
 * Card object to be passed to SagePay to support a transaction.
 * Reasonable validation is done at creation.
 */

use Exception;
use UnexpectedValueException;

use Academe\SagePayMsg\Message\CardIdentifierResponse;
use Academe\SagePayMsg\Message\SessionKeyResponse;

class Card implements PaymentMethodInterface
{
    protected $sessionKey;
    protected $cardIdentifier;

    public function __construct(SessionKeyResponse $sessionKey, CardIdentifierResponse $cardIdentifier)
    {
        $this->cardIdentifier = $cardIdentifier;
        $this->sessionKey = $sessionKey;
    }

    /**
     * Return the body partial for message construction.
     */
    public function getBody()
    {
        return array(
            'card' => array(
                'merchantSessionKey' => $this->sessionKey->getMerchantSessionKey(),
                'cardIdentifier' => $this->cardIdentifier->getCardIdentifier(),
            ),
        );
    }
}
