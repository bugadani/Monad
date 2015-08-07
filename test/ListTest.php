<?php

namespace Monad;

class ListTest extends \PHPUnit_Framework_TestCase
{

    public function testFiniteList()
    {
        $result = ListMonad::unit([1, 2, 3])
                           ->bind(function ($value) {
                               return 2 * $value;
                           })
                           ->bind(function ($value) {
                               return $value - 1;
                           });

        $this->assertEquals([1, 3, 5], $result->extract());
    }

    public function testPower()
    {
        $square = function ($a) {
            return [[$a * $a, $a * $a]];
        };
        $result = ListMonad::unit([2, 2])
                           ->bind($square);

        $this->assertEquals([[4, 4], [4, 4]], $result->extract());
    }

    public function testSumPairs()
    {
        $result = ListMonad::unit([0, 1, 2])
                           ->bind(function ($value) {
                               return ListMonad::unit([0, 1, 2])->bind(
                                   function ($value2) use ($value) {
                                       return $value + $value2;
                                   }
                               );
                           });

        $this->assertEquals([[0, 1, 2], [1, 2, 3], [2, 3, 4]], $result->extract());
    }

    public function testDoubleMultiDimensional()
    {
        $result = ListMonad::unit([[1, 2], [3, 4], [5, 6]])
                           ->bind(function ($value) {
                               return ListMonad::unit($value)
                                               ->bind(function ($value) {
                                                   return 2 * $value;
                                               });
                           });

        foreach ($result as $r) {
            $this->assertCount(2, $r);
        }
        $this->assertEquals([[2, 4], [6, 8], [10, 12]], $result->extract());

        $result = ListMonad::unit([[1, 2], [3, 4], [5, 6]])
                           ->bind(function ($value) {
                               return ListMonad::unit($value);
                           })
                           ->bind(function ($value) {
                               return 2 * $value;
                           });

        foreach ($result as $r) {
            $this->assertCount(2, $r);
        }
        $this->assertEquals([[2, 4], [6, 8], [10, 12]], $result->extract());
    }

    public function testBunnyGeneration()
    {
        $generation = function ($value) {
            return [$value, $value];
        };

        $result = ListMonad::unit(['bunny'])
                           ->bind($generation);

        $this->assertEquals(['bunny', 'bunny'], $result->extract());
        $this->assertEquals('List(bunny, bunny)', (string)$result);

        $result = ListMonad::unit(['bunny'])
                           ->bind($generation)
                           ->bind($generation);

        $this->assertEquals(
            ['bunny', 'bunny', 'bunny', 'bunny'],
            $result->extract()
        );
        $this->assertEquals('List(bunny, bunny, bunny, bunny)', (string)$result);
    }

    public function testListOfMaybe()
    {
        $result = ListMonad::unit([1, 5, null, 10])
                           ->bind(Maybe::$unit)
                           ->bind(function ($value) {
                               return 2 * $value;
                           });

        $this->assertEquals('List(Just(2), Just(10), Nothing, Just(20))', (string)$result);
    }

    public function testInfiniteList()
    {
        $infiniteIterator  = new \InfiniteIterator(new \ArrayIterator([1, 2, 3, 4]));
        $infiniteListMonad = ListMonad::unit($infiniteIterator)
                                      ->bind(function ($value) {
                                          return $value * 2;
                                      });

        $expected = [2, 4, 6, 8, 2, 4, 6, 8, 2, 4];
        $i        = 0;
        foreach ($infiniteListMonad as $item) {
            if ($i === 10) {
                break;
            }
            $this->assertEquals($expected[ $i++ ], $item);
        }
    }

    /**
     * @see https://github.com/ircmaxell/monad-php
     */
    public function testGetAuthor()
    {
        $index = function ($key) {
            return function ($array) use ($key) {
                return isset($array[ $key ]) ? $array[ $key ] : null;
            };
        };
        $posts = [
            ["title" => "foo", "author" => ["name" => "Bob", "email" => "bob@example.com"]],
            ["title" => "bar", "author" => ["name" => "Tom", "email" => "tom@example.com"]],
            ["title" => "baz"],
            ["title" => "biz", "author" => ["name" => "Mark", "email" => "mark@example.com"]],
        ];
        $monad = new ListMonad($posts);

        $names = $monad->bind(Maybe::$unit)
                       ->bind($index("author"))
                       ->bind($index("name"))
                       ->extract();

        $this->assertEquals(['Bob', 'Tom', null, 'Mark'], $names);
    }
}
