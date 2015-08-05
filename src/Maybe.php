<?php

namespace Monad;

abstract class Maybe extends Monad
{
    /**
     * @param $value
     * @return Monad
     */
    public static function of($value)
    {
        if ($value === null) {
            return new Nothing();
        }

        return new Just($value);
    }

    public static function nothing()
    {
        return new Nothing();
    }

    public static function just($value)
    {
        return Just::of($value);
    }
}