<?php

namespace Monad\Promise;

use Monad\Promise;

class Rejected extends PromiseState
{

    public function getCallback(callable $onFulfilled = null, callable $onRejected = null)
    {
        return $onRejected;
    }

    public function getState()
    {
        return 'rejected';
    }
}