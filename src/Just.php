<?php

namespace Monad;

class Just extends Maybe
{
    public function bind(callable $transform)
    {
        return $this->runTransform($transform);
    }

    public function __toString()
    {
        return "Just({$this->value})";
    }
}