<?php

namespace Academe\SagePay\Psr7;

use ReflectionClass;
use Psr\Http\Message\MessageInterface;

/**
 * Shared (Request and Response) abstract message.
 */

abstract class AbstractMessage
{
    /**
     * Get an array of constants in this [late-bound] class, with an optional prefix.
     * @param null $prefix
     * @return array
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
     * @param $prefix
     * @param $suffix
     * @return mixed|null
     */
    public static function constantValue($prefix, $suffix)
    {
        $name = strtoupper($prefix . '_' . $suffix);

        if (defined("static::$name")) {
            return constant("static::$name");
        }

        return null;
    }

    /**
     * Parse the body of a PSR-7 message, into a PHP array.
     * @param $message MessageInterface
     * @return array|mixed
     */
    public function parseBody(MessageInterface $message)
    {
        $data = [];

        if ($message->hasHeader('Content-Type')) {
            // Sage Pay returns responses with JSON, but the notify callback is Form URL encoded,
            // so we need to cater for both.

            if ($message->getHeaderLine('Content-Type') === 'application/x-www-form-urlencoded') {
                parse_str((string)$message->getBody(), $data);
            } elseif ($message->getHeaderLine('Content-Type') === 'application/json') {
                $data = json_decode($message->getBody());
            }
        }

        return $data;

    }
}
