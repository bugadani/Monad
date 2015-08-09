<?php

namespace Monad\Promise;

use Monad\Promise;

class Fulfilled extends ResolutionResult
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