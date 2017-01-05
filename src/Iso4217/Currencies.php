<?php

namespace Academe\SagePay\Psr7\Iso4217;

/**
 * Handles the list of all currencies.
 */

class Currencies
{
    protected $currencies = [];

    /**
     * Pull in the full list of currencies maintained in current.php
     */
    public function __construct()
    {
        $this->currencies = include(__DIR__ . '/current.php');
    }

    /**
     * Return the full list.
     */
    public function all()
    {
        return $this->currencies;
    }

    /**
     * Just return one currency, keyed by ISO code.
     * Optionally limit the result to just one of the fields: alphabeticCode, currency, minorUnit or numericCode.
     */
    public function get($code, $field = null)
    {
        if (isset($this->currencies[$code])) {
            return empty($field) ? $this->currencies[$code] : $this->currencies[$code][$field];
        } else {
            return null;
        }
    }
}
