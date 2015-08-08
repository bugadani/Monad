<?php

namespace Monad\Promise;

use Monad\Promise;

class Rejected extends Promise
{
    private $reason;
    private $parent;

    public function __construct(Promise $parent, $reason)
    {
        $this->parent = $parent;
        $this->reason = $reason;
    }

    public function fulfill($value)
    {
        throw new \BadMethodCallException('The Promise is already rejected');
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        if ($onRejected === null) {
            //2.2.7.4
            return $this;
        }
        try {
            //2.2.7.1
            $returned = $onRejected($this->reason);

            return Promise::resolve($returned, new Promise($this->parent));
        } catch (\Exception $e) {
            //2.2.7.2
            return new Rejected($this->parent, $e);
        }
    }

    public function getState()
    {
        return 'rejected';
    }

    public function __toString()
    {
        return "Promise({$this->reason})";
    }
}