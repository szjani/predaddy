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

namespace predaddy\domain;

use ArrayIterator;
use PHPUnit_Framework_TestCase;
use predaddy\eventhandling\DirectEventBus;

require_once __DIR__ . '/User.php';
require_once __DIR__ . '/IncrementedEvent.php';
require_once __DIR__ . '/UserCreated.php';

/**
 * Description of AggregateRootTest
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class AggregateRootTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DirectEventBus
     */
    private $eventBus;

    protected function setUp()
    {
        parent::setUp();
        $this->eventBus = new DirectEventBus(__CLASS__);
        AggregateRoot::setEventBus($this->eventBus);
    }

    public function testCallHandleMethod()
    {
        $user = new User();
        self::assertEquals(User::DEFAULT_VALUE, $user->value);

        $eventRaised = false;
        $this->eventBus->registerClosure(
            function (IncrementedEvent $event) use (&$eventRaised) {
                $eventRaised = true;
            }
        );

        $user->increment();
        self::assertEquals(2, $user->value);
        self::assertTrue($eventRaised);
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

        $user = new User();
        self::assertEquals(User::DEFAULT_VALUE, $user->value);
        self::assertInstanceOf(UserCreated::className(), $lastEvent);

        // increment 3 times
        $user->increment();
        $user->increment();
        $user->increment();

        // 1 create and 3 increment events raised
        self::assertEquals(4, count($raisedEvents));

        /* @var $replayedUser User */
        $replayedUser = User::createEmpty();
        $replayedUser->loadFromHistory(new ArrayIterator($raisedEvents));

        // the two user have the same values
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
