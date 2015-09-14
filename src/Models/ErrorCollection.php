<?php namespace Academe\SagePayMsg\Models;

/**
 * A collection of errors, normally validation errors.
 * Once we have  validation errors collected in here, we can sort them into
 * property names (fields), error types etc.
 */

use Exception;
use UnexpectedValueException;

use ArrayIterator;

use Academe\SagePayMsg\Helper;

class ErrorCollection implements \IteratorAggregate
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
        $errors = Helper::structureGet($data, 'errors', null);

        $collection = new static();

        if (is_array($errors)) {
            foreach($errors as $error) {
                $collection->add(Error::fromData($error));
            }
        } else {
            $collection->add(Error::fromData($data));
        }

        return($collection);
    }
}
