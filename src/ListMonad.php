<?php

namespace Monad;

use Traversable;

class ListMonad extends Monad implements \IteratorAggregate
{
    public static $unit = [__CLASS__, 'unit'];

    protected function __construct($value)
    {
        if ($value instanceof \IteratorAggregate) {
            $value = $value->getIterator();
        } else if (!$value instanceof \Traversable) {
            if (!is_array($value)) {
                $value = [$value];
            }
            $value = new \ArrayIterator($value);
        }

        parent::__construct($value);
    }

    /**
     * @param callable $transform
     *
     * @return Monad
     */
    public function bind(callable $transform)
    {
        return new ListMonad(new ArrayCallbackIterator($this->value, $transform));
    }

    public function extract()
    {
        $extractIfMonad = function ($value) {
            if ($value instanceof Monad) {
                $value = $value->extract();
            }

            return $value;
        };

        return array_map($extractIfMonad, iterator_to_array($this));
    }

    public function __toString()
    {
        $values   = iterator_to_array($this);
        $elements = implode(', ', $values);

        return "List({$elements})";
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return $this->value;
    }
}