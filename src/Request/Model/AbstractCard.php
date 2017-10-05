<?php

namespace Academe\SagePay\Psr7\Request\Model;

/**
 * Common functionality between all types of request card payment methods.
 */

use Academe\SagePay\Psr7\Response\CardIdentifier;
use Academe\SagePay\Psr7\Response\SessionKey;

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
}
