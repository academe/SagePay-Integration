<?php namespace Academe\SagePayMsg\Models;

/**
 * A collection of errors, normally validation errors.
 * Once we have  validation errors collected in here, we can sort them into
 * property names (fields), error types etc.
 */

use Exception;
use UnexpectedValueException;

use ArrayIterator;

// FIXME: we are only extending AbstractMessage to get at the helper methods.
use Academe\SagePayMsg\Message\AbstractMessage;

class ErrorCollection extends AbstractMessage implements \IteratorAggregate
{
    protected $items = [];

    public function __construct(array $items = [])
    {
        $this->items = array_values($items);
    }

    public function add($item)
    {
        $this->items[] = $item;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    public static function fromData($data)
    {
        $errors = static::structureGet($data, 'errors', []);

        $collection = new static();

        if (is_array($errors)) {
            foreach($errors as $error) {
                $collection->add(Error::fromData($error));
            }
        }

        return($collection);
    }
}
