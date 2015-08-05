<?php

namespace Monad;

class Identity extends Monad
{
    public static $unit = [__CLASS__, 'unit'];

    public function bind(callable $transform)
    {
        return $this->runTransform($transform);
    }

    public function __toString()
    {
        return "Identity({$this->value})";
    }
}