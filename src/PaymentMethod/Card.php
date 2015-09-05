<?php namespace Academe\SagePayJs\PaymentMethod;

/**
 * Card object to be passed to SagePay to support a transaction.
 * Reasonable validation is done at creation.
 */

use Exception;
use UnexpectedValueException;

use Academe\SagePayJs\Models\CardIdentifier;
use Academe\SagePayJs\Models\SessionKey;

class Card implements PaymentMethodInterface
{
    protected $sessionKey;
    protected $cardIdentifier;

    public function __construct(SessionKey $sessionKey, CardIdentifier $cardIdentifier)
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
