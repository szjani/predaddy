<?php
declare(strict_types=1);

namespace predaddy\presentation;

use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public function testGetters()
    {
        $property = 'property';
        $order = new Order(Direction::$ASC, $property);
        self::assertTrue($order->getDirection()->equals(Direction::$ASC));
    }

    public function testEquals()
    {
        $property = 'property';
        $order = new Order(Direction::$ASC, $property);

        $order2 = new Order(Direction::$ASC, $property);
        self::assertTrue($order->equals($order));
        self::assertTrue($order->equals($order2));

        self::assertFalse($order->equals(null));
    }

    public function testToString()
    {
        $property = 'name';
        $order = new Order(Direction::$ASC, $property);
        self::assertEquals(
            'predaddy\presentation\Order{name, ASC}',
            $order->toString()
        );
    }
}
