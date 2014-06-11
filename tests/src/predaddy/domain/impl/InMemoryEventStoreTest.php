<?php
/*
 * Copyright (c) 2012-2014 Szurovecz János
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

namespace predaddy\domain\impl;

use predaddy\domain\CreateEventSourcedUser;
use predaddy\domain\DomainTestCase;
use predaddy\domain\EventSourcedUser;
use predaddy\domain\Increment;

/**
 * @package predaddy\domain\impl
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class InMemoryEventStoreTest extends DomainTestCase
{
    /**
     * @var InMemoryEventStore
     */
    private $eventStore;

    protected function setUp()
    {
        parent::setUp();
        $this->eventStore = new InMemoryEventStore();
    }

    public function test()
    {
        $user = new EventSourcedUser(new CreateEventSourcedUser());
        $user->increment(new Increment());
        $events = $this->getAndClearRaisedEvents();
        foreach ($events as $event) {
            $this->eventStore->persist($event);
        }
        $returnedEvents = $this->eventStore->getEventsFor(EventSourcedUser::className(), $user->getId());
        self::assertCount(2, $returnedEvents);
        self::assertSame($events[0], $returnedEvents[0]);
        self::assertSame($events[1], $returnedEvents[1]);

        $this->eventStore->createSnapshot(EventSourcedUser::className(), $user->getId());
        $returnedSnapshot = $this->eventStore->loadSnapshot(EventSourcedUser::className(), $user->getId());
        self::assertEquals($user, $returnedSnapshot);

        $user->increment(new Increment());
        $events = $this->getAndClearRaisedEvents();
        foreach ($events as $event) {
            $this->eventStore->persist($event);
        }
        $returnedEvents = $this->eventStore->getEventsFor(
            EventSourcedUser::className(),
            $user->getId(),
            $returnedSnapshot->getStateHash()
        );
        self::assertCount(1, $returnedEvents);
        self::assertSame($events[0], $returnedEvents[0]);
    }
}
