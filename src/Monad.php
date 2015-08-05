<?php

namespace Monad;

abstract class Monad
{
    /**
     * @param $value
     * @return Monad
     */
    public static function of($value)
    {
        return new static($value);
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
        $retVal = $transform($this->value);

        if (!$retVal instanceof Monad) {
            $retVal = static::of($retVal);
        }

        return $retVal;
    }

    /**
     * @param callable $transform
     * @return Monad
     */
    public abstract function bind(callable $transform);

    public abstract function __toString();
}