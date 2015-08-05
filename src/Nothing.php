<?php

namespace Monad;

class Nothing extends Maybe
{
    public static $unit = [__CLASS__, 'unit'];

    public static function unit($value = null)
    {
        return new Nothing();
    }

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