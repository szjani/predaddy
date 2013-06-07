<?php
/*
 * Copyright (c) 2013 Szurovecz János
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

namespace predaddy\eventhandling;

use PHPUnit_Framework_TestCase;

require_once 'SimpleEvent.php';
require_once 'SimpleEventHandler.php';
require_once 'AllEventHandler.php';
require_once 'DeadEventHandler.php';
require_once 'MultipleSameEventHandler.php';

/**
 * Description of DirectEventBusTest
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class DirectEventBusTest extends PHPUnit_Framework_TestCase
{
    protected $bus;

    public function setUp()
    {
        $this->bus = new DirectEventBus('default');
    }

    public function testTwoHandlerPost()
    {
        $event1 = new SimpleEvent();
        $event2 = new SimpleEvent();

        $simpleEventHandler = new SimpleEventHandler();
        $allEventHandler = new AllEventHandler();
        $this->bus->register($simpleEventHandler);
        $this->bus->register($allEventHandler);

        $this->bus->post($event1);

        self::assertSame($event1, $simpleEventHandler->lastEvent);
        self::assertSame($event1, $allEventHandler->lastEvent);

        $this->bus->unregister($simpleEventHandler);
        $this->bus->unregister($allEventHandler);
        $this->bus->post($event2);
        self::assertSame($event1, $simpleEventHandler->lastEvent);
        self::assertSame($event1, $allEventHandler->lastEvent);
    }

    public function testDeadEventHandling()
    {
        $expectedEvent = $event = new SimpleEvent();
        $deadEventHandler = new DeadEventHandler();

        $this->bus->register($deadEventHandler);
        $this->bus->post($event);

        self::assertSame($expectedEvent, $deadEventHandler->lastEvent->getEvent());
    }

    public function testMultipleSaveEventHandler()
    {
        $multipleSameEventHandler = new MultipleSameEventHandler();
        $this->bus->register($multipleSameEventHandler);

        $event = new SimpleEvent();
        $this->bus->post($event);

        self::assertSame($event, $multipleSameEventHandler->lastEvent);
        self::assertSame($event, $multipleSameEventHandler->lastEvent2);
        self::assertSame($event, $multipleSameEventHandler->lastEvent3);
        self::assertSame($event, $multipleSameEventHandler->lastEvent4);

        $event2 = new DeadEvent($event);
        $this->bus->post($event2);

        self::assertSame($event, $multipleSameEventHandler->lastEvent);
        self::assertSame($event2, $multipleSameEventHandler->lastEvent2);
        self::assertSame($event, $multipleSameEventHandler->lastEvent3);
        self::assertSame($event2, $multipleSameEventHandler->lastEvent4);
    }
}
