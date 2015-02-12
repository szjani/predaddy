<?php
/*
 * Copyright (c) 2012-2014 Janos Szurovecz
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

namespace predaddy\domain\eventsourcing;

use ArrayIterator;
use precore\util\UUID;
use predaddy\domain\AggregateId;
use predaddy\domain\DecrementedEvent;
use predaddy\domain\GenericAggregateId;
use predaddy\domain\DomainEvent;
use predaddy\domain\DomainTestCase;
use predaddy\domain\EventStore;
use predaddy\domain\IncrementedEvent;
use predaddy\domain\UserCreated;
use predaddy\inmemory\InMemoryEventStore;

class EventSourcingRepositoryTest extends DomainTestCase
{
    private static $ANY_EVENTS;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var EventSourcingRepository
     */
    private $repository;

    protected function setUp()
    {
        parent::setUp();
        $this->eventStore = $this->getMock('\predaddy\domain\EventStore');
        $this->repository = new EventSourcingRepository($this->eventStore);
        self::$ANY_EVENTS = new ArrayIterator();
    }

    /**
     * @return AggregateId
     */
    protected function createRandomAggregateId()
    {
        return new GenericAggregateId(UUID::randomUUID()->toString(), EventSourcedUser::className());
    }

    public function testGetEventStorage()
    {
        self::assertSame($this->eventStore, $this->repository->getEventStore());
    }

    public function testLoad()
    {
        $aggregateId = $this->createRandomAggregateId();

        $aggregate = new EventSourcedUser(new CreateEventSourcedUser());
        $this->expectedEvents($aggregateId, $this->getAndClearRaisedEvents());

        $replayedAggregate = $this->repository->load($aggregateId);
        self::assertEquals($aggregate->getId(), $replayedAggregate->getId());
        self::assertEquals($aggregate->value, $replayedAggregate->value);
    }

    public function testSave()
    {
        $version = 3;

        $aggregate = new EventSourcedUser(new CreateEventSourcedUser());
        $aggregate->increment(new Increment());

//        $this->eventStore
//            ->expects(self::once())
//            ->method('saveChanges')
//            ->will(
//                self::returnCallback(
//                    function ($className, Iterator $events) use ($version) {
//                        $events->rewind();
//                        EventSourcingRepositoryTest::assertEquals(EventSourcedUser::className(), $className);
//                        EventSourcingRepositoryTest::assertInstanceOf(UserCreated::className(), $events->current());
//                        $events->next();
//                        EventSourcingRepositoryTest::assertInstanceOf(
//                            IncrementedEvent::className(),
//                            $events->current()
//                        );
//                    }
//                )
//            );

        $events = $this->getAndClearRaisedEvents();

        $this->repository->save($aggregate, $version);

        self::assertCount(2, $events);
        self::assertInstanceOf(UserCreated::className(), $events[0]);
        self::assertInstanceOf(IncrementedEvent::className(), $events[1]);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfNoEventsForThatAggregate()
    {
        $aggregateId = $this->createRandomAggregateId();
        $this->expectedEvents($aggregateId, self::$ANY_EVENTS);
        $this->repository->load($aggregateId);
    }

    /**
     * @test
     */
    public function shouldBeLoadedFromSnapshot()
    {
        $aggregateId = $this->createRandomAggregateId();
        $eventStore = $this->getMock('\predaddy\domain\eventsourcing\SnapshotEventStore');
        $this->expectedEvents($aggregateId, new ArrayIterator([new IncrementedEvent($aggregateId)]), $eventStore);
        $repository = new EventSourcingRepository($eventStore);
        $eventStore
            ->expects(self::once())
            ->method('loadSnapshot')
            ->with($aggregateId);
        $repository->load($aggregateId);
    }

    public function testSetVersionAndAggregateIdFields()
    {
        $userId = null;
        $version = null;
        $this->eventBus->registerClosure(
            function (UserCreated $event) use (&$userId, &$version) {
                $userId = $event->aggregateId();
                $version = $event->stateHash();
            }
        );
        $createCommand = new CreateEventSourcedUser(null, 0);
        $user = new EventSourcedUser($createCommand);
        $this->repository->save($user);
        self::assertNotNull($userId);
        self::assertEquals($version, $user->stateHash());

        $this->eventBus->registerClosure(
            function (DecrementedEvent $event) use (&$userId, &$version) {
                $userId = $event->aggregateId();
                $version = $event->stateHash();
            }
        );
        $user->decrement(new Decrement());
        $this->repository->save($user);
        self::assertNotNull($userId);
        self::assertEquals($version, $user->stateHash());
    }

    public function testWithInMemoryMessageBus()
    {
        $this->eventStore = new InMemoryEventStore();
        $this->repository = new EventSourcingRepository($this->eventStore);
        $this->eventBus->registerClosure(
            function (DomainEvent $event) {
                $this->eventStore->persist($event);
            }
        );
        $user = new EventSourcedUser(new CreateEventSourcedUser());
        $user->increment(new Increment());
        $this->eventStore->createSnapshot($user->getId());
        self::assertEquals($user, $this->eventStore->loadSnapshot($user->getId()));

        $user->increment(new Increment());
        $loadedUser = $this->repository->load($user->getId());
        self::assertEquals($user, $loadedUser);
    }

    private function expectedEvents(AggregateId $aggregateId, \Iterator $events, EventStore $eventStore = null)
    {
        if ($eventStore === null) {
            $eventStore = $this->eventStore;
        }
        $eventStore
            ->expects(self::once())
            ->method('getEventsFor')
            ->with($aggregateId)
            ->will(self::returnValue($events));
    }
}
