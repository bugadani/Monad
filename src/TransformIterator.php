<?php

namespace Monad;

class TransformIterator extends \IteratorIterator
{
    private $callback;

    public function __construct($value, callable $callback = null)
    {
        parent::__construct($value);
        $this->callback = $callback;
    }

    public function current()
    {
        $callback = $this->callback;

        return $callback(parent::current());
    }
}