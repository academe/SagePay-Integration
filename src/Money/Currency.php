<?php

namespace Academe\SagePay\Psr7\Money;

/**
 * Defines a currency.
 * Only supports currencies that SagePay supports.
 * TODO: create a CurrencyInterface for this.
 */

use Academe\SagePay\Psr7\Iso4217\Currencies;
use UnexpectedValueException;

class Currency
{
    /**
     * @var string ISO 4217 currency code
     */
    protected $code;

    /**
     * Array of all currencies, initialised on instantiation.
     */
    protected $all_currencies;

    /**
     * @param string $code The ISO 4217 three-character currency code
     */
    public function __construct($code)
    {
        $this->all_currencies = new Currencies();

        if ($this->all_currencies->get($code)) {
            $this->code = $code;
        } else {
            throw new UnexpectedValueException(sprintf('Unsupported currency code "%s"', $code));
        }
    }

    /**
     * @return string The ISO 4217 three-character currency code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * TODO: renamed this to minorUnits?
     * @return mixed The number of digits in the decimal subunit
     */
    public function getDigits()
    {
        return ($this->all_currencies->get($this->code, 'minorUnit'));
    }

    /**
     * The symbols will be one or more UTF-8 characters.
     * getName and getSymbol are handy for display and logging, but not essential,
     * so they are not a part of the interface.
     *
     * @return string The name of the currency
     */
    public function getName()
    {
        return ($this->all_currencies->get($this->code, 'currency'));
    }

    /**
     * @return string The currency symbol, made of one or more UTF-8 characters
     * @deprec No longer supported
     */
    public function getSymbol()
    {
        return null;
    }
}
