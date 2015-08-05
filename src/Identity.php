<?php

namespace Monad;

class Identity extends Monad
{
    public function bind(callable $transform)
    {
        return $this->runTransform($transform);
    }

    public function __toString()
    {
        return "Identity({$this->extract()})";
    }
}