<?php

namespace Monad;

use Monad\Promise\Fulfilled;
use Monad\Promise\Rejected;

/**
 * Promise monad that implements the Promise/A+ specification
 */
class Promise extends Monad
{
    public static $unit = [__CLASS__, 'resolve'];

    public static function resolve($value, Promise $promise = null)
    {
        if ($promise === null) {
            $promise = new Promise();
        }
        if ($value === $promise) {
            throw new \InvalidArgumentException('$promise === $value');
        } elseif ($value instanceof Promise) {
            $value->then(
                [$promise, 'fulfill'],
                [$promise, 'reject']
            );
        } elseif (is_object($value) && method_exists($value, 'then')) {
            try {
                $value->then(
                    function ($value) use ($promise) {
                        Promise::resolve($promise, $value);
                    },
                    [$promise, 'reject']
                );
            } catch (\Exception $e) {
                if ($promise->getState() === 'pending') {
                    $promise->reject($e);
                }
            }
        } else {
            $promise->fulfill($value);
        }

        return $promise;
    }

    /**
     * @var callable
     */
    private $onFulfilled = null;

    /**
     * @var callable
     */
    private $onRejected = null;

    /**
     * @var Promise[]
     */
    private $children = [];

    /**
     * @var Promise
     */
    private $resolved;

    /**
     * @var Promise
     */
    private $parent;

    /**
     * @var bool
     */
    private $tasksRunning = false;

    /**
     * @var \SplQueue
     */
    private $tasks;

    public function __construct(Promise $parent = null)
    {
        if ($parent === null) {
            $this->tasks = new \SplQueue();
            $this->tasks->setIteratorMode(\SplQueue::IT_MODE_DELETE);
        } else {
            $this->parent = $parent;
            $this->tasks  = $parent->tasks;
        }
    }

    /**
     * @param callable $onFulfilled
     * @param callable $onRejected
     *
     * @return Promise
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        if ($this->resolved === null) {

            $child              = new Promise($this);
            $child->onFulfilled = $onFulfilled;
            $child->onRejected  = $onRejected;

            $this->children[] = $child;
        } else {
            $this->postTask(
                function () use ($onFulfilled, $onRejected, &$child) {
                    $child = $this->resolved->then($onFulfilled, $onRejected);
                }
            );
            $this->runTasks();
        }

        return $child;
    }

    public function fulfill($value)
    {
        if ($this->resolved !== null) {
            throw new \BadMethodCallException('The Promise has already been resolved');
        }

        $this->resolved = new Fulfilled($this, $value);
        foreach ($this->children as $child) {
            if ($child->onFulfilled !== null) {
                $this->postTask(
                    function () use ($child, $value) {
                        try {
                            $onFulfilled = $child->onFulfilled;
                            Promise::resolve($onFulfilled($value), $child);
                        } catch (\Exception $e) {
                            $child->reject($e);
                        }
                    }
                );
            } else {
                $child->fulfill($value);
            }
        }
        $this->runTasks();
    }

    public function reject($reason)
    {
        if ($this->resolved !== null) {
            throw new \BadMethodCallException('The Promise has already been resolved');
        }
        $this->resolved = new Rejected($this, $reason);
        foreach ($this->children as $child) {
            if ($child->onRejected !== null) {
                $this->postTask(
                    function () use ($child, $reason) {
                        try {
                            $onRejected = $child->onRejected;
                            Promise::resolve($child, $onRejected($reason));
                        } catch (\Exception $e) {
                            $child->reject($e);
                        }
                    }
                );
            } else {
                $child->reject($reason);
            }
        }
        $this->runTasks();
    }

    public function getState()
    {
        if ($this->resolved === null) {
            return 'pending';
        }

        return $this->resolved->getState();
    }

    protected function postTask($task)
    {
        $this->tasks->enqueue($task);
    }

    private function runTasks()
    {
        if ($this->parent === null) {
            if ($this->tasksRunning) {
                return;
            }
            $this->tasksRunning = true;
            foreach ($this->tasks as $task) {
                /** @var callable $task */
                $task();
            }
            $this->tasksRunning = false;
        } else {
            $parent = $this->parent;
            while ($parent->parent !== null) {
                $parent = $parent->parent;
            }
            $parent->runTasks();
        }
    }

    public function bind(callable $onFulfilled, callable $onRejected = null)
    {
        return $this->then($onFulfilled, $onRejected);
    }

    public function __toString()
    {
        if ($this->resolved === null) {
            return 'Promise';
        }

        return $this->resolved->__toString();
    }
}