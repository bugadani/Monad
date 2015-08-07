<?php

namespace Monad;

use Traversable;

class ListMonad extends Monad implements \Iterator
{
    public static $unit = [__CLASS__, 'unit'];

    /**
     * @var \Closure
     */
    private $transformation;

    /**
     * @var \ArrayIterator
     */
    private $transformed;

    public function __construct($value, callable $transform = null)
    {
        if ($value instanceof \IteratorAggregate) {
            $value = $value->getIterator();
        } else if (!$value instanceof \Traversable) {
            if (!is_array($value)) {
                $value = [$value];
            }
            $value = new \ArrayIterator($value);
        }
        //Start with the unit transformation
        parent::__construct($value);

        $this->transformation = $transform;
        if ($transform === null) {
            $this->transformed = $value;
        } else {
            $this->transformed = new \ArrayIterator();
        }
    }

    private function transform($current)
    {
        $transform = $this->transformation;
        if ($current instanceof Monad) {
            $return = $current->bind($transform);
        } else {
            $return = $transform($current);
        }

        return $return;
    }

    /**
     * @param $transformed
     */
    private function append($transformed)
    {
        if (is_array($transformed)) {
            foreach ($transformed as $value) {
                $this->transformed->append($value);
            }
        } else {
            $this->transformed->append($transformed);
        }
    }

    private function iterable($transformed)
    {
        return is_array($transformed) || $transformed instanceof \Traversable;
    }

    /**
     * @param callable $transform
     * @return Monad
     */
    public function bind(callable $transform)
    {
        return new ListMonad($this, $transform);
    }

    public function extract()
    {
        return array_map(
            function ($value) {
                if ($value instanceof Monad) {
                    $value = $value->extract();
                }

                return $value;
            },
            iterator_to_array($this)
        );
    }

    public function __toString()
    {
        $values   = iterator_to_array($this);
        $elements = implode(', ', $values);

        return "List({$elements})";
    }

    public function rewind()
    {
        $this->transformed->rewind();
    }

    public function current()
    {
        return $this->transformed->current();
    }

    public function key()
    {
        return $this->transformed->key();
    }

    public function next()
    {
        $this->transformed->next();
        if (!$this->transformed->valid()) {
            $this->value->next();
        }
    }

    public function valid()
    {
        if (!$this->transformed->valid()) {
            if ($this->value->valid()) {
                $this->append(
                    $this->transform($this->value->current())
                );
            }
        }

        return $this->transformed->valid();
    }
}