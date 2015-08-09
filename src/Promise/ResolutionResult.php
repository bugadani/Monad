<?php

namespace Monad\Promise;

use Monad\Promise;

abstract class ResolutionResult
{
    private $value;

    public function __construct($value)
    {
        $this->value  = $value;
    }

    abstract public function getCallback(callable $onFulfilled = null, callable $onRejected = null);

    abstract public function getState();

    public function __toString()
    {
        return "Promise({$this->value})";
    }

    public function extract()
    {
        return $this->value;
    }
}