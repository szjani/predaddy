<?php
/*
 * Copyright (c) 2013 Janos Szurovecz
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace predaddy\presentation;

use PHPUnit_Framework_TestCase;

class SortTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Sort
     */
    private $sort;

    /**
     * @var Order
     */
    private $order1;

    /**
     * @var Order
     */
    private $order2;

    public function setUp()
    {
        $this->order1 = new Order(Direction::$ASC, 'prop1');
        $this->order2 = new Order(Direction::$DESC, 'prop2');
        $orders = [$this->order1, $this->order2];
        $this->sort = new Sort($orders);
    }

    public function testCount()
    {
        self::assertEquals(2, $this->sort->count());
    }

    public function testGetOrderFor()
    {
        self::assertTrue($this->order2->equals($this->sort->getOrderFor('prop2')));
        self::assertNull($this->sort->getOrderFor('invalid'));
    }

    public function testAndSort()
    {
        $res = $this->sort->andSort(null);
        self::assertSame($this->sort, $res);

        $newOrder = new Order(Direction::$DESC, 'prop3');
        $orders = [$newOrder];
        $sort = new Sort($orders);
        $res = $this->sort->andSort($sort);
        self::assertEquals(2, $this->sort->count());
        self::assertEquals(3, $res->count());
        self::assertSame($newOrder, $res->getOrderFor('prop3'));
    }

    public function testEquals()
    {
        self::assertFalse($this->sort->equals(null));
        self::assertTrue($this->sort->equals($this->sort));

        $sort2 = new Sort([$this->order1, $this->order2]);
        self::assertTrue($this->sort->equals($sort2));
    }

    public function testCreate()
    {
        $properties = ['prop1', 'prop2'];
        $sort = Sort::create($properties, Direction::$DESC);

        self::assertEquals(2, $sort->count());
        self::assertEquals(Direction::$DESC, $sort->getOrderFor('prop1')->getDirection());
        self::assertEquals(Direction::$DESC, $sort->getOrderFor('prop2')->getDirection());

        $sort = Sort::create($properties);
        self::assertEquals(Direction::$ASC, $sort->getOrderFor('prop1')->getDirection());
    }

    public function testToString()
    {
        $properties = ['prop1', 'prop2'];
        $sort = Sort::create($properties, Direction::$DESC);
        self::assertEquals(
            'predaddy\presentation\Sort{orders={0=predaddy\presentation\Order{property=prop1, direction=DESC}, 1=predaddy\presentation\Order{property=prop2, direction=DESC}}}',
            $sort->toString()
        );
    }
}
