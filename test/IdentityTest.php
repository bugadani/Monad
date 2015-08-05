<?php

namespace Monad;

class IdentityTest extends \PHPUnit_Framework_TestCase
{
    public function testIdentity()
    {
        $result = Identity::unit(1)->bind(
            function ($value) {
                return Identity::unit(2)->bind(
                    function ($value2) use ($value) {
                        return $value + $value2;
                    });
            });

        $this->assertEquals(3, $result->extract());
    }

    public function testIdentityChained()
    {
        $result = Identity::unit(1)
                          ->bind(function ($value) {
                              return 2 * $value;
                          })
                          ->bind(function ($value) {
                              return $value + 1;
                          });

        $this->assertEquals(3, $result->extract());
    }
}
