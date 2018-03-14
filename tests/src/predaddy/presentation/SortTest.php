<?php
declare(strict_types=1);

namespace predaddy\presentation;

use PHPUnit\Framework\TestCase;

class SortTest extends TestCase
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
            'predaddy\presentation\Sort{orders=[0=predaddy\presentation\Order{prop1, DESC}, 1=predaddy\presentation\Order{prop2, DESC}]}',
            $sort->toString()
        );
    }
}
