<?php

namespace Academe\SagePay\Psr7\Response\Model;

/**
 * Card details response object.
 */

class Card implements JsonSerializable
{
    /**
     * @var Tokenised card.
     */
    protected $cardIdentifier;

    /**
     * @var Flag indicates this is a reusable card identifier; it has been used before.
     */
    protected $reusable;

    /**
     * @var Flag indicates this card identifier must be saved on next use, so it can be used again.
     */
    protected $save;

    /**
     * @var Captured (safe) details for the card.
     * TODO: move these to response card class (this is requestcard class).
     */
    protected $cardType;
    protected $lastFourDigits;
    protected $expiryDate; // MMYY
}
