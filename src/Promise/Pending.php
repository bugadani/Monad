<?php

namespace Monad\Promise;

use Monad\Promise;

class Pending extends ResolutionResult
{
    /**
     * @var Promise[]
     */
    private $children = [];

    /**
     * @var \SplObjectStorage
     */
    private $onFulfilled;

    /**
     * @var \SplObjectStorage
     */
    private $onRejected;

    public function __construct(Promise $promise, TaskQueue $tasks)
    {
        parent::__construct($promise, $tasks, null);
        $this->onFulfilled = new \SplObjectStorage();
        $this->onRejected  = new \SplObjectStorage();
    }

    public function reject($reason)
    {
        $resolved = new Rejected($this->promise, $this->tasks, $reason);
        foreach ($this->children as $child) {
            $onRejected = $this->onRejected[ $child ];
            if ($onRejected !== null) {
                $this->postResolveTask($child, $onRejected, $reason);
            } else {
                $child->reject($reason);
            }
        }
        $this->runTasks();

        return $resolved;
    }

    public function fulfill($value)
    {
        $resolved = new Fulfilled($this->promise, $this->tasks, $value);
        foreach ($this->children as $child) {
            $onFulfilled = $this->onFulfilled[ $child ];
            if ($onFulfilled !== null) {
                $this->postResolveTask($child, $onFulfilled, $value);
            } else {
                $child->fulfill($value);
            }
        }
        $this->tasks->runTasks();

        return $resolved;
    }

    public function getCallback(callable $onFulfilled = null, callable $onRejected = null)
    {
        return null;
    }

    public function getState()
    {
        return 'pending';
    }

    public function __toString()
    {
        return 'Promise';
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        $child = new Promise($this->promise);
        $this->onFulfilled->attach($child, $onFulfilled);
        $this->onRejected->attach($child, $onRejected);
        $this->children[] = $child;

        return $child;
    }
}