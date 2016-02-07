<?php namespace Academe\SagePayMsg\Money;

/**
 * Value object for the amount, based on extending the moneyphp/money package.
 * Both v1.3 and v3.x (in alpha) should work.
 * The getAmount() method of Money\Money already returns the amount in the required
 * minor unit format.
 */

use Money\Money as MoneyMoney;

class Money extends MoneyMoney implements AmountInterface
{
    public function getCurrencyCode()
    {
        $currency = $this->getCurrency();

        // To support Money ~1.x and ~3.x
        if (method_exists($currency, 'getCode')) {
            return $currency->getCode();
        } else {
            return $currency->getName();
        }
    }
}
