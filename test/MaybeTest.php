<?php

namespace Monad;

class MaybeTest extends \PHPUnit_Framework_TestCase
{
    public function testJust()
    {
        $nothing = Just::of(null);
        $maybeNull = Maybe::of(null);

        $this->assertInstanceOf('Monad\\Nothing', $nothing);
        $this->assertInstanceOf('Monad\\Nothing', $maybeNull);
    }

    public function testMaybe()
    {
        $result = Maybe::of(1)
                       ->bind(function ($value) {
                           return null;
                       })
                       ->bind(function ($value) {
                           $this->fail();

                           return Maybe::just(3);
                       });

        $this->assertInstanceOf('Monad\\Nothing', $result);
    }
}
