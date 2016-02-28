<?php

namespace Academe\SagePay\Psr7;

use ReflectionClass;

/**
 * Shared (Request and Response) abstract message.
 */

abstract class AbstractMessage
{
    /**
     * Get an array of constants in this [late-bound] class, with an optional prefix.
     */
    public static function constantList($prefix = null)
    {
        $reflection = new ReflectionClass(__CLASS__);
        $constants = $reflection->getConstants();

        if (isset($prefix)) {
            $result = [];
            $prefix = strtoupper($prefix);
            foreach($constants as $key => $value) {
                if (strpos($key, $prefix) === 0) {
                    $result[$key] = $value;
                }
            }
            return $result;
        } else {
            return $constants;
        }
    }

    /**
     * Get a class constant value based on suffix and prefix.
     * Returns null if not found.
     */
    public static function constantValue($prefix, $suffix)
    {
        $name = strtoupper($prefix . '_' . $suffix);

        if (defined("static::$name")) {
            return constant("static::$name");
        }
    }
}
