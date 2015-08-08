<?php

namespace Monad;

class PromiseTest extends \PHPUnit_Framework_TestCase
{

    /**
     * This test
     */
    public function testConstantStackSize()
    {
        $parent = new Promise();
        $p      = $parent;

        $count  = 10;
        $called = 0;

        for ($i = 0; $i < $count; $i++) {
            $p = $p->then(
                function ($v) use (&$called) {
                    $currentDepth = xdebug_get_stack_depth();
                    $called++;
                    if ($v != -1) {
                        $this->assertEquals($v, $currentDepth);
                    }

                    return $currentDepth;
                },
                function (\Exception $e) {
                    $this->fail($e->getMessage());
                }
            );
        }

        $parent->fulfill(-1);
        $this->assertEquals($count, $called);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testThatAPromiseCanNotBeResolvedMultipleTimes()
    {
        $promise = new Promise();
        $promise->fulfill(1);
        $promise->fulfill(1);
    }

    public function testThatStateTransitions()
    {
        $promise = new Promise();
        $this->assertEquals('pending', $promise->getState());

        $promise = new Promise();
        $promise->fulfill(1);
        $this->assertEquals('fulfilled', $promise->getState());

        $promise = new Promise();
        $promise->reject(1);
        $this->assertEquals('rejected', $promise->getState());
    }

    public function testThatThenRejectsIfCallbackThrowsException()
    {
        $promise = new Promise();
        $result  = $promise->then(
            function ($value) {
                throw new \Exception($value);
            }
        );
        $promise->fulfill(2);
        $this->assertEquals('rejected', $result->getState());
    }

    public function testThatThenCanBeCalledAfterAPromiseIsFulfilled()
    {
        $promise = new Promise();
        $called  = 0;

        $promise->then(
            function ($value) {
                throw new \Exception($value);
            }
        );
        $promise->fulfill(2);
        $promise->then(
            function () use (&$called) {
                $called++;
            }
        );
        $this->assertEquals(1, $called);
    }

    public function testThatThenCanBeCalledAfterAPromiseIsRejected()
    {
        $promise = new Promise();
        $called  = 0;

        $promise->then(
            function ($value) {
                throw new \Exception($value);
            }
        );
        $promise->reject(2);
        $promise->then(
            null,
            function () use (&$called) {
                $called++;
            }
        );
        $this->assertEquals(1, $called);
    }

    public function testPromiseAsMonad()
    {
        $result = Promise::resolve(2)->bind(
            function ($value) {
                return Promise::resolve(3)->bind(
                    function ($value2) use ($value) {
                        return $value + $value2;
                    }
                );
            }
        );

        $this->assertEquals('Promise(5)', (string)$result);
    }
}