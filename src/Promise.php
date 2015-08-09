<?php

namespace Monad;

use Monad\Promise\Pending;
use Monad\Promise\ResolutionResult;
use Monad\Promise\TaskQueue;

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
     * @var ResolutionResult
     */
    private $resolved;

    /**
     * @var Promise
     */
    private $parent;

    /**
     * @var TaskQueue
     */
    private $tasks;

    public function __construct(Promise $parent = null)
    {
        if ($parent === null) {
            $this->tasks = new TaskQueue();
        } else {
            $this->parent = $parent->parent === null ? $parent : $parent->parent;
            $this->tasks  = $parent->tasks;
        }
        $this->resolved = new Pending($this, $this->tasks);
    }

    public function getTaskQueue()
    {
        return $this->tasks;
    }

    /**
     * @param callable $onFulfilled
     * @param callable $onRejected
     *
     * @return Promise
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        return $this->resolved->then($onFulfilled, $onRejected);
    }

    public function fulfill($value)
    {
        $this->resolved = $this->resolved->fulfill($value);
    }

    public function reject($reason)
    {
        $this->resolved = $this->resolved->reject($reason);
    }

    public function bind(callable $onFulfilled, callable $onRejected = null)
    {
        return $this->then($onFulfilled, $onRejected);
    }

    public function extract()
    {
        return $this->resolved->extract();
    }

    public function getState()
    {
        return $this->resolved->getState();
    }

    public function __toString()
    {
        return $this->resolved->__toString();
    }
}