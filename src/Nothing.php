<?php

namespace Monad;

class Nothing extends Maybe
{
    public function __construct()
    {
    }

    public function bind(callable $transform)
    {
        return $this;
    }

    public function __toString()
    {
        return "Nothing";
    }
}