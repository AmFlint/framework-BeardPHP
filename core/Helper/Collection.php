<?php

namespace Helper;


use Exception;

class Collection implements \Iterator
{
    public $items = [];

    public function __construct($data, $modelName)
    {
        $this->items = ModelHandler::generateEntities($data, $modelName);
    }

    public function rewind()
    {
        reset($this->items);
    }

    public function current()
    {
        return current($this->items);
    }

    public function key()
    {
        return key($this->items);
    }

    public function next()
    {
        next($this->items);
    }

    public function valid()
    {
        return key($this->items) !== null;
    }

    /**
     * Method to convert collection's model object to array items
     * @return array
     * @throws Exception
     */
    public function toArray()
    {
        $collectionArray = [];
        foreach ($this->items as $item)
        {
            if (!method_exists($item, 'getAttributes')) {
                throw new Exception('Calling getAttributes on non model');
            }
            array_push($collectionArray, $item->getAttributes());
        }
        return $collectionArray;
    }

    /**
     * Get the number of items stored in the current Collection item
     * @return int - number of items the Collection holds.
     */
    public function count()
    {
        return count($this->items);
    }
}
