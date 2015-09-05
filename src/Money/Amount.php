<?php namespace Academe\SagePayJs\Money;

/**
 * Value object for the amount, in the appropriate currency.
 * TODO: Check currencies are supported.
 * TODO: Accepty amount in raw integer (smallest units) and decimal form.
 * TODO: need to know number of DP for each currency foy conversion.
 * TODO: Output amount in smallest units form.
 * TODO: get all this into the interface, so it can be interfaced with other currency libraries.
 */

use Exception;
use UnexpectedValueException;

class Amount implements AmountInterface
{
    // Integer value in the smallest units.
    protected $amount;
    protected $currency;

    /**
     * If $amount is an integer, or a string with no decimal points,
     * then take is as the minimum units.
     * If $amount is a float, or a string with a decimal pointm then
     * take it as a major unit that needs converting.
     */
    public function __construct($currency, $amount = 0)
    {
        $this->currency = $currency;
        $this->amount = $amount;
    }

    /**
     *
     */
    public function withCurrency($currency)
    {
    }

    /**
     * Allow the decimal notation of the currency to be supplied,
     * as a float or a string.
     */
    public function withMajorUnit($amount)
    {
    }

    /**
     * Allow the smallest units of the currency to be supplied,
     * as an integer or a string.
     */
    public function withMinorUnit($amount)
    {
    }

    // TODO: magic method to support e.g. $amount = Amount::EUR(995)
    // equivalent to: new Amount(new Currency('EUR'), 995)
}
