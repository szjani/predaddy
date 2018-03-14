<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;

class ClosureTest extends TestCase
{
    public function testClosure()
    {
        $bus = MessageBusObjectMother::createAnnotatedBus();
        $calledF1 = 0;
        $calledF2 = 0;
        $closure1 = function (SimpleMessage $message) use (&$calledF1) {
            $calledF1++;
        };
        $closure2 = function (Message $message) use (&$calledF2) {
            $calledF2++;
        };
        $bus->registerClosure($closure1);
        $bus->registerClosure($closure2);

        $bus->post(new SimpleMessage());
        self::assertEquals(1, $calledF1);
        self::assertEquals(1, $calledF2);

        $bus->unregisterClosure($closure1);
        $bus->post(new SimpleMessage());
        self::assertEquals(1, $calledF1);
        self::assertEquals(2, $calledF2);

        $bus->unregisterClosure($closure1);
        $bus->post(new DeadMessage(new SimpleMessage()));
        self::assertEquals(1, $calledF1);
        self::assertEquals(3, $calledF2);
    }

    public function testNotThrownException()
    {
        $bus = MessageBusObjectMother::createAnnotatedBus();
        $called = false;
        $closure = function (SimpleMessage $message) use (&$called) {
            $called = true;
            throw new InvalidArgumentException('Must be caught by the bus!');
        };
        $bus->registerClosure($closure);
        $bus->post(new SimpleMessage());
        self::assertTrue($called);
    }
}
