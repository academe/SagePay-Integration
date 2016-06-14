<?php namespace Academe\SagePay\Psr7\Response;

/**
 * A collection of errors, normally validation errors.
 * Once we have  validation errors collected in here, we can sort them into
 * property names (fields), error types etc.
 */

use Psr\Http\Message\ResponseInterface;
use ArrayIterator;
use Academe\SagePay\Psr7\Helper;
use Academe\SagePay\Psr7\Model\Error;

class ErrorCollection extends AbstractResponse implements \IteratorAggregate
{
    /**
     * @var array
     */
    protected $items = array();

    /**
     * @param $data
     * @return $this
     */
    protected function setData($data)
    {
        // A list of errors will be provided in a wrapping "errors" element.
        $errors = Helper::dataGet($data, 'errors', null);

        // If there was no "errors" wrapper, then assume what we have is a single error,
        // provided there is a "code" or "statusCode" element at a minimum.

        if (! isset($errors) && ! empty(Helper::dataGet($data, 'code', Helper::dataGet($data, 'statusCode', null)))) {
            $this->add(Error::fromData($data, $this->getHttpCode()));
        } elseif (is_array($errors)) {
            foreach($errors as $error) {
                // The $error may be an Error object or an array.
                $this->add(Error::fromData($error, $this->getHttpCode()));
            }
        }

        return $this;
    }

    /**
     * Add a new error to the collection.
     * This collection is not a value object. Perhaps it should be: withError()?
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
     * Return errors for a specific property.
     * Use null to return errors without a property reference.
     * Returns ErrorCollection
     *
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

    /**
     * @return array All errors in the collection.
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public static function isResponse($data)
    {
        return is_array(Helper::dataGet($data, 'errors'))
            || Helper::dataGet($data, 'status') == 'Error'
            || Helper::dataGet($data, 'status') == 'Invalid';
    }

    /**
     * @inheritdoc
     */
    public function isError()
    {
        return true;
    }

    /**
     * Reduce the object to an array so it can be serialised.
     * @return array
     */
    public function jsonSerialize()
    {
        $return = [
            'httpCode' => $this->getHttpCode(),
            'errors' => $this->all(),
        ];

        return $return;
    }
}
