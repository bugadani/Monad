<?php

namespace Monad;

abstract class Monad
{

    /**
     * @param $value
     *
     * @return Monad
     */
    public static function unit($value)
    {
        if (!$value instanceof static) {
            $value = new static($value);
        }

        return $value;
    }

    protected $value;

    protected function __construct($value)
    {
        $this->value = $value;
    }

    public function extract()
    {
        return $this->value;
    }

    protected function runTransform($transform)
    {
        return static::unit($transform($this->value));
    }

    /**
     * @param callable $transform
     *
     * @return Monad
     */
    public abstract function bind(callable $transform);

    public abstract function __toString();
}