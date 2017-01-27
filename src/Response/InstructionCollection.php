<?php

namespace Academe\SagePay\Psr7\Response;

/**
 * A a collection of instructions.
 */

use Academe\SagePay\Psr7\Factory\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Academe\SagePay\Psr7\Helper;

class InstructionCollection extends AbstractResponse implements \IteratorAggregate
{
    /**
     * The list of instructions.
     * @var array
     */
    protected $items = [];

    /**
     * 
     */
    public function setData($data, $httpCode = null)
    {
        if ($httpCode) {
            $this->setHttpCode($httpCode);
        }

        // A list of errors will be provided in a wrapping "errors" element.
        $instructions = Helper::dataGet($data, 'instructions', null);

        if (is_array($instructions)) {
            // The instructions will hopefully be an array of instruction objects.
            // Use the ResponseFactory to decide what each one is, and create the
            // appropriate object.

            foreach($instructions as $instruction) {
                $this->add(ResponseFactory::fromData($instruction, $httpCode));
            }
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'instructions' => $this->items,
        ];
    }

    /**
     * Add a new instruction to the collection.
     * This collection is not a value object. Perhaps it should be: withError()?
     * TODO: create an abstract for the instruction responses.
     *
     * @param Void $item An Error instance to add
     */
    public function add(Void $item)
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
     * @return int Count of instructions in the collection
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @return array all instructions in the collection.
     */
    public function all()
    {
        return $this->items;
    }
}
