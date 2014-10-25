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

namespace predaddy\messagehandling;

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;

class ClosureTest extends PHPUnit_Framework_TestCase
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
