<?php namespace Academe\SagePayMsg\Model;

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
    /**
     * @var array
     */
    protected $items = array();

    /**
     * @param array $items Initial array of Error instances
     */
    public function __construct(array $items = [])
    {
        // Add each item individually, providing some validation.
        foreach($items as $item) {
            $this->add($item);
        }
    }

    /**
     * Add a new error to the collection.
     *
     * @param Error $item An Error instance to add
     */
    public function add(Error $item)
    {
        $this->items[] = $item;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @param array|object $data List of Error instances or array of error details
     *
     * @return static Collection of Error instances
     */
    public static function fromData($data, $httpCode = null)
    {
        $errors = Helper::structureGet($data, 'errors', null);

        $collection = new static();

        if (is_array($errors)) {
            foreach($errors as $error) {
                // The $error may be an Errot object or an array.
                $collection->add(Error::fromData($error, $httpCode));
            }
        } else {
            $collection->add(Error::fromData($data, $httpCode));
        }

        return($collection);
    }

    /**
     * Return errors for a specific property, including null for
     * errors without a property reference..
     * Returns ErrorCollection
     */
    /**
     * @param null|string $property_name The property name or null to get errors without a property name
     *
     * @return static A collection of zero or more Error objects
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
     * @return array Array of all properties the errors in this collection report on
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
     * @return int Count of errors in the collection
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @return bool True if there are any errors in the collection, otherwise False
     */
    public function hasErrors()
    {
        return $this->count() > 0;
    }

    /**
     * @return Error The first error in the collection.
     */
    public function first()
    {
        return reset($this->items);
    }
}
