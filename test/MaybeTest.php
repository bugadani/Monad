<?php

namespace Monad;

class MaybeTest extends \PHPUnit_Framework_TestCase
{
    public function testMaybe()
    {
        $result = Just::unit(1)
                      ->bind(function ($value) {
                          return null;
                      })
                      ->bind(function ($value) {
                          $this->fail();

                          return 3;
                      });

        $this->assertInstanceOf('Monad\\Nothing', $result);

        $result = Just::unit(Nothing::unit());
        $this->assertInstanceOf('Monad\\Nothing', $result);
    }
}
