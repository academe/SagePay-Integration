<?php namespace Academe\SagePayJs\PaymentMethod;

/**
 * Card object to be passed to SagePay to support a transaction.
 * Reasonable validation is done at creation.
 */

use Exception;
use UnexpectedValueException;

use Academe\SagePayJs\Message\CardIdentifierResponse;
use Academe\SagePayJs\Message\SessionKeyResponse;

class Card implements PaymentMethodInterface
{
    protected $sessionKey;
    protected $cardIdentifier;

    public function __construct(SessionKeyResponse $sessionKey, CardIdentifierResponse $cardIdentifier)
    {
        $this->cardIdentifier = $cardIdentifier;
        $this->sessionKey = $sessionKey;
    }

    public function toArray()
    {
        return array(
            'card' => array(
                'merchantSessionKey' => $this->sessionKey->getMerchantSessionKey(),
                'cardIdentifier' => $this->cardIdentifier->getCardIdentifier(),
            ),
        );
    }
}
