<?php

namespace Monad;

use Traversable;

class ListMonad extends Monad implements \IteratorAggregate
{
    private $transform;

    public function __construct($value)
    {
        if (is_array($value)) {
            $value = new \ArrayObject($value);
        } else if (!$value instanceof \Traversable) {
            throw new \InvalidArgumentException('$value must be an array or a Traversable object');
        }
        //Start with the unit transformation
        $this->transform = function ($value) {
            return $value;
        };
        parent::__construct($value);
    }

    /**
     * @param callable $transform
     * @return Monad
     */
    public function bind(callable $transform)
    {
        $list = new ListMonad($this->value);
        $list->transform = self::compose($transform, $this->transform);

        return $list;
    }

    public function extract()
    {
        $return = [];
        foreach ($this as $value) {
            if ($value instanceof Monad) {
                $value = $value->extract();
            }
            $return[] = $value;
        }

        return $return;
    }

    public function __toString()
    {
        $values = [];
        foreach ($this as $value) {
            $values[] = $value;
        }
        $elements = implode(', ', $values);

        return "List({$elements})";
    }

    public function getIterator()
    {
        return new TransformIterator($this->value, $this->transform);
    }

    private static function compose($transform, $oldTransform)
    {
        return function ($value) use ($oldTransform, $transform) {
            return $transform($oldTransform($value));
        };
    }
}