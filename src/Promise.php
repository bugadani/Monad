<?php

namespace Monad;

use Monad\Promise\Fulfilled;
use Monad\Promise\Rejected;
use Monad\Promise\ResolutionResult;

/**
 * Promise monad that implements the Promise/A+ specification
 */
class Promise extends Monad
{
    public static $unit = [__CLASS__, 'resolve'];

    public static function resolve($value, Promise $promise = null)
    {
        if ($value === $promise) {
            throw new \InvalidArgumentException('$promise === $value');
        }
        if ($value instanceof Promise) {
            return $value;
        } else {
            if ($promise === null) {
                $promise = new Promise();
            }
            if (is_object($value) && method_exists($value, 'then')) {
                try {
                    $value->then(
                        function ($value) use ($promise) {
                            Promise::resolve($value, $promise);
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
     * @var ResolutionResult
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
            $this->parent = $parent->parent === null ? $parent : $parent->parent;
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
        $child              = new Promise($this);
        $child->onFulfilled = $onFulfilled;
        $child->onRejected  = $onRejected;

        if ($this->resolved === null) {
            $this->children[] = $child;
        } else {
            $callback = $this->resolved->getCallback($onFulfilled, $onRejected);
            if ($callback === null) {
                $child->resolved = $this->resolved;
            } else {
                $this->postResolveTask($child, $this->resolved->extract());
                $this->runTasks();
            }
        }

        return $child;
    }

    public function fulfill($value)
    {
        if ($this->resolved !== null) {
            throw new \BadMethodCallException('The Promise has already been resolved');
        }

        $this->resolved = new Fulfilled($value);
        foreach ($this->children as $child) {
            if ($child->onFulfilled !== null) {
                $this->postResolveTask($child, $value);
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

        $this->resolved = new Rejected($reason);
        foreach ($this->children as $child) {
            if ($child->onRejected !== null) {
                $this->postResolveTask($child, $reason);
            } else {
                $child->reject($reason);
            }
        }
        $this->runTasks();
    }

    private function postResolveTask(Promise &$child, $value)
    {
        $function = $this->resolved->getCallback($child->onFulfilled, $child->onRejected);
        $this->postTask(
            function () use (&$child, $function, $value) {
                try {
                    $child = Promise::resolve($function($value), $child);
                } catch (\Exception $e) {
                    $child->reject($e);
                }
            }
        );
    }

    private function postTask($task)
    {
        $this->tasks->enqueue($task);
    }

    private function runTasks()
    {
        if ($this->parent === null) {
            if (!$this->tasksRunning) {
                $this->tasksRunning = true;
                foreach ($this->tasks as $task) {
                    /** @var callable $task */
                    $task();
                }
                $this->tasksRunning = false;
            }
        } else {
            $this->parent->runTasks();
        }
    }

    public function bind(callable $onFulfilled, callable $onRejected = null)
    {
        return $this->then($onFulfilled, $onRejected);
    }

    public function extract()
    {
        if ($this->resolved === null) {
            return null;
        }

        return $this->resolved->extract();
    }

    public function getState()
    {
        if ($this->resolved === null) {
            return 'pending';
        }

        return $this->resolved->getState();
    }

    public function __toString()
    {
        if ($this->resolved === null) {
            return 'Promise';
        }

        return $this->resolved->__toString();
    }
}