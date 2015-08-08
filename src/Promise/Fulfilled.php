<?php

namespace Monad\Promise;

use Monad\Promise;

class Fulfilled extends Promise
{
    private $parent;

    public function __construct(Promise $parent, $value)
    {
        $this->parent = $parent;
        $this->value  = $value;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        if ($onFulfilled === null) {
            //2.2.7.3
            return $this;
        }
        try {
            //2.2.7.1
            $returned = $onFulfilled($this->value);
            return Promise::resolve($returned, new Promise($this->parent));
        } catch (\Exception $e) {
            //2.2.7.2
            return new Rejected($this->parent, $e);
        }
    }

    public function reject($reason)
    {
        throw new \BadMethodCallException('The Promise has already been fulfilled');
    }

    public function getState()
    {
        return 'fulfilled';
    }

    public function __toString()
    {
        return "Promise({$this->value})";
    }
}