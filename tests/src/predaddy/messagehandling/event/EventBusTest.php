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

namespace predaddy\messagehandling\event;

use ArrayIterator;
use PHPUnit_Framework_TestCase;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\DefaultFunctionDescriptorFactory;
use RuntimeException;

require_once 'SimpleEvent.php';

class EventBusTest extends PHPUnit_Framework_TestCase
{
    public function testNoHandler()
    {
        $functionDescriptorFactory = new EventFunctionDescriptorFactory();
        $handlerDescriptorFactory = new AnnotatedMessageHandlerDescriptorFactory($functionDescriptorFactory);
        $transactionManager = $this->getMock('\trf4php\ObservableTransactionManager');
        $commandBus = new EventBus(
            $handlerDescriptorFactory,
            $transactionManager
        );

        $event = new SimpleEvent();
        $called = false;
        $commandBus->registerClosure(
            function(Event $incomingEvent) use (&$called, $event) {
                $called = true;
                EventBusTest::assertEquals($incomingEvent->getMessageIdentifier(), $incomingEvent->getEventIdentifier());
                EventBusTest::assertSame($event, $incomingEvent);
            }
        );
        $commandBus->post($event);
        self::assertTrue($called);
    }
}
