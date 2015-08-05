<?php

namespace Monad;

abstract class Maybe extends Monad
{
    /**
     * @param $value
     * @return Monad
     */
    public static function unit($value)
    {
        if ($value === null) {
            return new Nothing();
        }

        return new Just($value);
    }
}