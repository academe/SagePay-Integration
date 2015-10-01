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
    protected $items = array();

    public function __construct(array $items = [])
    {
        $this->items = array_values($items);
    }

    public function add(Error $item)
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

    /**
     * Return errors for a specific property, including null for
     * errors without a property reference..
     * Returns ErrorCollection
     */
    public function byProperty($property_name = null)
    {
        $result = new static();

        foreach($this as $error) {
            if ($property_name === $error->getProperty()) {
                $result->add($error);
            }
        }

        return $result;
    }

    /**
     * Return an array of properties.
     */
    public function getProperties()
    {
        $result = array();

        foreach($this as $error) {
            if ( ! in_array($error->getProperty(), $result)) {
                $result[] = $error->getProperty();
            }
        }

        return $result;
    }

    /**
     * Count of errors in the collection.
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Tells us if there are any errors in the collection.
     */
    public function hasErrors()
    {
        return $this->count() > 0;
    }
}
