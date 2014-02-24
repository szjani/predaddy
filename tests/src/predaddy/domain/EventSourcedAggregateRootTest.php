<?php
/*
 * Copyright (c) 2014 Szurovecz János
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

namespace predaddy\domain;

use ArrayIterator;
use PHPUnit_Framework_TestCase;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\event\EventBus;
use predaddy\messagehandling\event\EventFunctionDescriptorFactory;

require_once __DIR__ . '/EventSourcedUser.php';
require_once __DIR__ . '/IncrementedEvent.php';
require_once __DIR__ . '/UserCreated.php';

class EventSourcedAggregateRootTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EventBus
     */
    private $eventBus;

    protected function setUp()
    {
        parent::setUp();
        $functionDescFactory = new EventFunctionDescriptorFactory();
        $handlerDescFactory = new AnnotatedMessageHandlerDescriptorFactory($functionDescFactory);
        $transactionManager = $this->getMock('\trf4php\ObservableTransactionManager');
        $this->eventBus = new EventBus($handlerDescFactory, $transactionManager);
        AggregateRoot::setEventBus($this->eventBus);
    }

    public function testLoadFromHistory()
    {
        $raisedEvents = array();
        $lastEvent = null;

        $this->eventBus->registerClosure(
            function (DomainEvent $event) use (&$raisedEvents, &$lastEvent) {
                $raisedEvents[] = $event;
                $lastEvent = $event;
            }
        );

        $user = new EventSourcedUser();
        self::assertEquals(EventSourcedUser::DEFAULT_VALUE, $user->value);
        self::assertInstanceOf(UserCreated::className(), $lastEvent);

        // increment 3 times
        $user->increment();
        $user->increment();
        $user->increment();

        // 1 create and 3 increment events raised
        self::assertEquals(4, count($raisedEvents));

        /* @var $replayedUser EventSourcedUser */
        $replayedUser = EventSourcedUser::createEmpty();
        $replayedUser->loadFromHistory(new ArrayIterator($raisedEvents));

        // the two user have the same values
        self::assertNotNull($user->getId());
        self::assertEquals($user->getId(), $replayedUser->getId());
        self::assertEquals($user->value, $replayedUser->value);

        // increment only $replayedUser's value
        $replayedUser->increment();
        self::assertEquals($user->value + 1, $replayedUser->value);
        self::assertEquals(5, count($raisedEvents));

        // replay the last event coming from $replayedUser on the original user
        $user->loadFromHistory(new ArrayIterator(array($lastEvent)));
        self::assertEquals($user->value, $replayedUser->value);
    }
}
