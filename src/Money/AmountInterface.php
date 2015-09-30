<?php namespace Academe\SagePayMsg\Money;

/**
 * Amount interface, for carrying an amount and its currency.
 */

interface AmountInterface
{
    /**
     * Return the amount, always in integer minot units.
     * For example, return 123 for amount 1.23 USD.
     */
    public function getAmount();

    /**
     * Return the currency ISO code.
     * For example "GBP" for UK Pound (£).
     */
    public function getCurrencyCode();
}
