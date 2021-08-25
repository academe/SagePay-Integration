<?php

namespace Academe\Opayo\Pi\Request\Model;

/**
 * Common functionality between all types of request card payment methods.
 */

use Academe\Opayo\Pi\Response\CardIdentifier;
use Academe\Opayo\Pi\Response\SessionKey;

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
