<?php

namespace Monad;

class ListTest extends \PHPUnit_Framework_TestCase
{
    public function testFiniteList()
    {
        $result = ListMonad::of([1, 2, 3])
                           ->bind(function ($value) {
                               return 2 * $value;
                           })
                           ->bind(function ($value) {
                               return $value - 1;
                           });

        $this->assertEquals([1, 3, 5], $result->extract());
    }

    public function testSumPairs()
    {
        $result = ListMonad::of([0, 1, 2])
                           ->bind(function ($value) {
                               return ListMonad::of([0, 1, 2])->bind(
                                   function ($value2) use ($value) {
                                       return $value + $value2;
                                   }
                               );
                           });

        $this->assertEquals([[0, 1, 2], [1, 2, 3], [2, 3, 4]], $result->extract());
    }

    public function testDoubleMultiDimensional()
    {
        $result = ListMonad::of([[1, 2], [3, 4], [5, 6]])
                           ->bind(function ($value) {
                               return ListMonad::of($value)->bind(function ($value) {
                                   return 2 * $value;
                               });
                           });

        $this->assertEquals([[2, 4], [6, 8], [10, 12]], $result->extract());
    }

    public function testListOfMaybe()
    {
        $result = ListMonad::of([Maybe::of(1), Maybe::of(5), Maybe::of(null), Maybe::of(10)])
                           ->bind(function ($value) {
                               return $value->bind(function ($value) {
                                   return 2 * $value;
                               });
                           });

        $this->assertEquals('List(Just(2), Just(10), Nothing, Just(20))', (string)$result);
    }

    public function testBigList()
    {
        $infiniteIterator  = new \InfiniteIterator(new \ArrayIterator([1, 2, 3, 4]));
        $infiniteListMonad = ListMonad::of($infiniteIterator)
                                      ->bind(function ($value) {
                                          return $value * 2;
                                      });

        $expected = [2, 4, 6, 8, 2, 4, 6, 8, 2, 4];
        $i = 0;
        foreach ($infiniteListMonad as $item) {
            if ($i === 10) {
                break;
            }
            $this->assertEquals($expected[$i++], $item);
        }
    }
}
