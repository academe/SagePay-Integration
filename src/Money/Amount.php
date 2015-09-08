<?php namespace Academe\SagePayMsg\Money;

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
     * $amount is an integer, or a string with no decimal points;
     * it is treated as a minor unit (e.g. pence or cents).
     */
    public function __construct(Currency $currency, $amount = 0)
    {
        $this->currency = $currency;

        $this->withMinorUnit($amount);
    }

    /**
     * Allow the decimal notation of the currency to be supplied,
     * as a float or a string.
     */
    public function withMajorUnit($amount)
    {
        if (is_int($amount) || is_float($amount) || (is_string($amount) && preg_match('/^[0-9]*\.[0-9]*$/', $amount))) {
            $amount = (float)$amount * pow(10, $this->currency->getDigits());

            if (floor($amount) != $amount) {
                // Too many decimal digits for the currency.
                throw new UnexpectedValueException(sprintf(
                    'Amount has too many decimal places. Minor unit %f should be an integer.',
                    $amount
                ));
            }

            $copy = clone $this;
            $copy->amount = (int)$amount;
            return $copy;
        } else {
        }
    }

    /**
     * Allow the smallest units of the currency to be supplied,
     * as an integer or a string.
     */
    public function withMinorUnit($amount)
    {
        if (is_int($amount) || (is_string($amount) && preg_match('/^[0-9]+$/', $string))) {
            $this->amount = (int)$amount;
        } else {
        }
    }

    // TODO: magic method to support e.g. $amount = Amount::EUR(995)
    // equivalent to: new Amount(new Currency('EUR'), 995)
    public static function __callStatic($name, $arguments)
    {
        try {
            $currency = new Currency($name);
        } catch (Exception $e) {
            $trace = debug_backtrace();
            throw new Exception(sprintf(
                'Call to undefined method $class::%s() in %s on line %d',
                get_called_class(),
                $trace[0]['file'],
                $trace[0]['line']
            ));
        }

        if (isset($arguments[0])) {
            return new static($currency, $arguments[0]);
        } else {
            return new static($currency);
        }
    }

    /**
     * Return the amount, always in minot units.
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Return the currency object.
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Return the currency ISO code.
     */
    public function getCurrencyCode()
    {
        return $this->currency->getCode();
    }
}
