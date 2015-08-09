<?php

namespace Monad\Promise;

use Monad\Promise;

abstract class ResolutionResult
{
    protected $tasks;
    protected $value;
    protected $promise;

    public function __construct(Promise $promise, TaskQueue $tasks, $value)
    {
        $this->tasks = $tasks;
        $this->promise= $promise;
        $this->value = $value;
    }

    abstract public function getCallback(callable $onFulfilled = null, callable $onRejected = null);

    abstract public function getState();

    protected function postResolveTask(Promise &$child, callable $callback, $value)
    {
        $this->tasks->enqueue(
            function () use (&$child, $callback, $value) {
                try {
                    $child = Promise::resolve($callback($value), $child);
                } catch (\Exception $e) {
                    $child->reject($e);
                }
            }
        );
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        $callback = $this->getCallback($onFulfilled, $onRejected);
        if ($callback === null) {
            return $this;
        } else {
            $child = new Promise($this->promise);

            $this->postResolveTask($child, $callback, $this->value);
            $this->tasks->runTasks();

            return $child;
        }
    }

    public function runTasks()
    {
        $this->tasks->runTasks();
    }

    public function reject($reason)
    {
        throw new \BadMethodCallException('The Promise has already been resolved');
    }

    public function fulfill($value)
    {
        throw new \BadMethodCallException('The Promise has already been resolved');
    }

    public function __toString()
    {
        return "Promise({$this->value})";
    }

    public function extract()
    {
        return $this->value;
    }
}