<?php

namespace Monad\Promise;

use Monad\Promise;

class Fulfilled extends PromiseState
{

    public function getCallback(callable $onFulfilled = null, callable $onRejected = null)
    {
        return $onFulfilled;
    }

    public function getState()
    {
        return 'fulfilled';
    }
}