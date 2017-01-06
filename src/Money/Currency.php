<?php

namespace Academe\SagePay\Psr7\Money;

/**
 * Defines a currency.
 * Only supports currencies that SagePay supports.
 * TODO: create a CurrencyInterface for this.
 */

use Academe\SagePay\Psr7\Iso4217\Currencies;
use UnexpectedValueException;

class Currency implements CurrencyInterface
{
    /**
     * @var string ISO 4217 currency code
     */
    protected $code;

    /**
     * Object holding all currencies, initialised on instantiation.
     * @var Academe\SagePay\Psr7\Iso4217\Currencies
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
     * Return a new instance of a specified currency.
     * e.g. Currency::GBP()
     */
    static public function __callStatic($method, $args) {
        return new static($method);
    }

    /**
     * @return string The ISO 4217 three-character currency code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return integer The number of digits in the decimal subunit (aka minor units)
     */
    public function getMinorUnits()
    {
        return ($this->all_currencies->get($this->code, 'minorUnit'));
    }

    /**
     * @return mixed The number of digits in the decimal subunit
     * @deprecated Use getMinorUnits()
     */
    public function getDigits()
    {
        return $this->getMinorUnits();
    }

    /**
     * The symbols will be one or more UTF-8 characters.
     * getName and getSymbol are handy for display and logging, but not essential,
     * so they are not a part of the interface.
     *
     * @return string The en-GB name of the currency
     */
    public function getName()
    {
        return ($this->all_currencies->get($this->code, 'currency'));
    }

    /**
     * @return string The currency symbol, made of one or more UTF-8 characters
     * @deprecated No longer supported
     */
    public function getSymbol()
    {
        return null;
    }
}
