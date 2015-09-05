<?php namespace Academe\SagePayJs\Models;

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
}
