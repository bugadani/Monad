<?php

namespace Monad;

class Just extends Maybe
{
    public static $unit = [__CLASS__, 'unit'];

    public function bind(callable $transform)
    {
        return $this->runTransform($transform);
    }

    public function __toString()
    {
        return "Just({$this->value})";
    }
}