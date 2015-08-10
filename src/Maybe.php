<?php

namespace Monad;

abstract class Maybe extends Monad
{
    public static $unit = [__CLASS__, 'unit'];

    /**
     * @param $value
     * @return Monad
     */
    public static function unit($value)
    {
        if ($value === null) {
            return Nothing::unit();
        }

        return new Just($value);
    }
}