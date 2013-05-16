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

namespace baseddd\eventhandling;

use PHPUnit_Framework_TestCase;
use precore\lang\ObjectClass;

require_once 'SimpleEvent.php';
require_once 'SimpleEventHandler.php';
require_once 'AllEventHandler.php';
require_once 'DeadEventHandler.php';

/**
 * Description of DirectEventBusTest
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class DirectEventBusTest extends PHPUnit_Framework_TestCase
{
    private $bus;

    public function setUp()
    {
        $this->bus = new DirectEventBus('default');
    }

    public function testTwoHandlerPost()
    {
        $event = new SimpleEvent();
        $simpleEventHandler = $this->getMock('baseddd\eventhandling\SimpleEventHandler');
        $allEventHandler = $this->getMock('baseddd\eventhandling\AllEventHandler');

        $simpleEventHandler
            ->expects(self::any())
            ->method('getObjectClass')
            ->will(self::returnValue(new ObjectClass($simpleEventHandler)));
        $simpleEventHandler
            ->expects(self::any())
            ->method('getClassName')
            ->will(self::returnValue('baseddd\eventhandling\SimpleEventHandler'));
        $simpleEventHandler
            ->expects(self::once())
            ->method('handle')
            ->with($event);

        $allEventHandler
            ->expects(self::any())
            ->method('getObjectClass')
            ->will(self::returnValue(new ObjectClass($allEventHandler)));
        $allEventHandler
            ->expects(self::any())
            ->method('getClassName')
            ->will(self::returnValue('baseddd\eventhandling\AllEventHandler'));
        $allEventHandler
            ->expects(self::once())
            ->method('handle')
            ->with($event);

        $this->bus->register($simpleEventHandler);
        $this->bus->register($allEventHandler);
        $this->bus->post($event);
        $this->bus->unregister($simpleEventHandler);
        $this->bus->unregister($allEventHandler);
        $this->bus->post($event);
    }

    public function testDeadEventHandling()
    {
        $expectedEvent = $event = new SimpleEvent();
        $deadEventHandler = $this->getMock('baseddd\eventhandling\DeadEventHandler');

        $deadEventHandler
            ->expects(self::any())
            ->method('getObjectClass')
            ->will(self::returnValue(new ObjectClass($deadEventHandler)));
        $deadEventHandler
            ->expects(self::any())
            ->method('getClassName')
            ->will(self::returnValue('baseddd\eventhandling\DeadEventHandler'));
        $deadEventHandler
            ->expects(self::once())
            ->method('handle')
            ->will(
                self::returnCallback(
                    function ($event) use ($expectedEvent) {
                        PHPUnit_Framework_TestCase::assertInstanceOf(DeadEvent::className(), $event);
                        PHPUnit_Framework_TestCase::assertSame($expectedEvent, $event->getEvent());
                    }
                )
            );

        $this->bus->register($deadEventHandler);
        $this->bus->post($event);
    }
}
