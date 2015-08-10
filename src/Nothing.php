<?php

namespace Monad;

class Nothing extends Maybe
{
    public static  $unit = [__CLASS__, 'unit'];
    private static $instance;

    public static function unit($value = null)
    {
        if (self::$instance === null) {
            self::$instance = new Nothing();
        }

        return self::$instance;
    }

    protected function __construct()
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