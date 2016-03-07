<?php namespace Academe\SagePay\Psr7;

/**
 * Shared message abstract.
 * Contains base methods that all messages will use.
 */

use Exception;
use UnexpectedValueException;

use DateTime;
use DateTimeZone;

abstract class Helper
{
    // SagePay date format, ISO 8601 with microseconds.
    // e.g. 2015-08-11T11:45:16.285+01:00

    const SAGEPAY_DATE_FORMAT = 'Y-m-d\TH:i:s.uP';

    /**
     * Get an element from a nested array, nested objects, or mix of the two.
     * The key uses "dot notation" to walk the nested data structure.
     * @param $data
     * @param $key
     * @param null $default
     * @return
     */
    public static function structureGet($data, $key, $default = null)
    {
        if (is_null($key) || trim($key) == '') {
            return $data;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_object($data) && isset($data->{$segment})) {
                $data = $data->{$segment};
                continue;
            }

            if (is_array($data) && array_key_exists($segment, $data)) {
                $data = $data[$segment];
                continue;
            }

            return static::_value($default);
        }

        return $data;
    }

    /**
     * @param $value
     */
    protected static function _value($value)
    {
        if ($value instanceof Closure) {
            return ${value}();
        } else {
            return $value;
        }
    }

    /**
     * Parse a date, returning a DateTime.
     * @param $date
     * @return DateTime
     */
    public static function parseDateTime($date)
    {
        try {
            if (is_string($date)) {
                // Supplied timestamp string should be ISO 8601 format.
                // Use a default UTC timezone for any relative dates that SagePay
                // may give us. Hopefully that won't be the case.

                $datetime = new DateTime($date, new DateTimeZone('UTC'));
            } elseif ($date instanceof DateTime) {
                $datetime = $date;
            } elseif (is_int($date)) {
                // Teat as a unix timestamp.
                $datetime = new DateTime();
                $datetime->setTimestamp($date);
            } else {
                throw new UnexpectedValueException('Unexpected datatype for datetime');
            }
        } catch(Exception $e) {
            throw new UnexpectedValueException('Unexpected time format', $e->getCode(), $e);
        }

        return $datetime;
    }
}
