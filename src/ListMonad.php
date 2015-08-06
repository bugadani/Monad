<?php

namespace Monad;

use Traversable;

class ListMonad extends Monad implements \Iterator
{
    private $transform;

    public function __construct($value, callable $transform = null)
    {
        if (is_array($value)) {
            $value = new \ArrayIterator($value);
        } else if (!$value instanceof \Traversable) {
            throw new \InvalidArgumentException('$value must be an array or a Traversable object');
        }
        if ($transform === null) {
            $transform = function ($value) {
                return $value;
            };
        }
        $this->transform = $transform;
        //Start with the unit transformation
        parent::__construct($value);
    }

    /**
     * @param callable $transform
     * @return Monad
     */
    public function bind(callable $transform)
    {
        $transform = function ($value) use ($transform) {
            if ($value instanceof Monad) {
                return $value->bind($transform);
            } else {
                return $transform($value);
            }
        };

        $monad            = new ListMonad($this, $this->transform);
        $monad->transform = $transform;

        return $monad;
    }

    public function extract()
    {
        $return = [];
        foreach ($this as $value) {
            $this->concatenate($value, $return);
        }
        foreach ($return as &$value) {
            if ($value instanceof Monad) {
                $value = $value->extract();
            }
        }

        return $return;
    }

    public function __toString()
    {
        $values = [];
        foreach ($this as $value) {
            $this->concatenate($value, $values);
        }
        $elements = implode(', ', $values);

        return "List({$elements})";
    }

    /**
     * @param $value
     * @param $values
     */
    private function concatenate($value, &$values)
    {
        if (is_array($value)) {
            $values = array_merge($values, $value);
        } else if ($value instanceof \Traversable) {
            $values = array_merge($values, iterator_to_array($value));
        } else {
            $values[] = $value;
        }
    }

    public function current()
    {
        $transform = $this->transform;

        return $transform($this->value->current());
    }

    public function next()
    {
        $this->value->next();
    }

    public function key()
    {
        return $this->value->key();
    }

    public function valid()
    {
        return $this->value->valid();
    }

    public function rewind()
    {
        $this->value->rewind();
    }
}