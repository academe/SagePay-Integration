<?php

namespace Academe\SagePay\Psr7\Request\Model;

/**
 * Common functionality between all types of request card payment methods.
 */

use Academe\SagePay\Psr7\Response\CardIdentifier;
use Academe\SagePay\Psr7\Response\SessionKey;
//use Academe\SagePay\Psr7\Helper;

abstract class AbstractCard implements PaymentMethodInterface
{

    /**
     * @var Supplied when sending card identifier.
     */
    protected $sessionKey;

    /**
     * @var Tokenised card.
     */
    protected $cardIdentifier;

    protected function setCardIdentifier($cardIdentifier)
    {
        // We just want the raw data from this object.
        if ($cardIdentifier instanceof CardIdentifier) {
            $cardIdentifier = $cardIdentifier->getCardIdentifier();
        }

        $this->cardIdentifier = $cardIdentifier;

        return $this;
    }

    protected function setSessionKey($sessionKey)
    {
        // We just want the raw data from this object.
        if ($sessionKey instanceof SessionKey) {
            $sessionKey = $sessionKey->getMerchantSessionKey();
        }

        $this->sessionKey = $sessionKey;

        return $this;
    }
}
