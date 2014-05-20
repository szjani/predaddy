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

namespace predaddy\domain;

use ArrayIterator;
use Iterator;
use PHPUnit_Framework_TestCase;
use precore\lang\ObjectClass;
use precore\util\UUID;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\eventhandling\EventBus;
use predaddy\eventhandling\EventFunctionDescriptorFactory;
use trf4php\NOPTransactionManager;

class EventSourcingRepositoryTest extends PHPUnit_Framework_TestCase
{
    private $eventStore;

    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @var EventSourcingRepository
     */
    private $repository;

    public function setUp()
    {
        $this->eventBus = new EventBus(
            new AnnotatedMessageHandlerDescriptorFactory(new EventFunctionDescriptorFactory())
        );
        $this->eventStore = $this->getMock('\predaddy\domain\EventStore');
        $this->repository = new EventSourcingRepository(
            EventSourcedUser::objectClass(),
            $this->eventBus,
            $this->eventStore
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidAggregateRootClass()
    {
        new EventSourcingRepository(ObjectClass::forName(__CLASS__), $this->eventBus, $this->eventStore);
    }

    public function testGetEventStorage()
    {
        self::assertSame($this->eventStore, $this->repository->getEventStore());
    }

    public function testLoad()
    {
        $aggregateId = new UUIDAggregateId(UUID::randomUUID());

        $aggregate = new EventSourcedUser(new CreateEventSourcedUser());

        $this->eventStore
            ->expects(self::once())
            ->method('getEventsFor')
            ->with(EventSourcedUser::className(), $aggregateId)
            ->will(self::returnValue($aggregate->getAndClearRaisedEvents()));

        $replayedAggregate = $this->repository->load($aggregateId);
        self::assertEquals($aggregate->getId(), $replayedAggregate->getId());
        self::assertEquals($aggregate->value, $replayedAggregate->value);
    }

    public function testSave()
    {
        $version = 3;

        $aggregate = new EventSourcedUser(new CreateEventSourcedUser());
        $aggregate->increment(new Increment());

        $this->eventStore
            ->expects(self::once())
            ->method('saveChanges')
            ->will(
                self::returnCallback(
                    function ($className, Iterator $events) use ($version) {
                        $events->rewind();
                        EventSourcingRepositoryTest::assertEquals(EventSourcedUser::className(), $className);
                        EventSourcingRepositoryTest::assertInstanceOf(UserCreated::className(), $events->current());
                        $events->next();
                        EventSourcingRepositoryTest::assertInstanceOf(
                            IncrementedEvent::className(),
                            $events->current()
                        );
                    }
                )
            );

        $events = [];
        $this->eventBus->registerClosure(
            function (DomainEvent $event) use (&$events) {
                $events[] = $event;
            }
        );

        $this->repository->save($aggregate, $version);

        self::assertCount(2, $events);
        self::assertInstanceOf(UserCreated::className(), $events[0]);
        self::assertInstanceOf(IncrementedEvent::className(), $events[1]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidId()
    {
        $aggregateId = new UUIDAggregateId(UUID::randomUUID());
        $this->eventStore
            ->expects(self::once())
            ->method('getEventsFor')
            ->with(EventSourcedUser::className(), $aggregateId)
            ->will(self::returnValue(new ArrayIterator([])));
        $this->repository->load($aggregateId);
    }

    public function testGetAggregateRootClass()
    {
        self::assertEquals(EventSourcedUser::objectClass(), $this->repository->getAggregateRootClass());
    }

    /**
     * @test
     */
    public function shouldBeLoadedFromSnapshot()
    {
        $aggregateId = new UUIDAggregateId(UUID::randomUUID());

        $events = new ArrayIterator([new IncrementedEvent($aggregateId, 1)]);
        $eventStore = $this->getMock('\predaddy\domain\SnapshotEventStore');
        $eventStore
            ->expects(self::once())
            ->method('getEventsFor')
            ->with(EventSourcedUser::className(), $aggregateId)
            ->will(self::returnValue($events));

        $repository = new EventSourcingRepository(EventSourcedUser::objectClass(), $this->eventBus, $eventStore);
        $eventStore
            ->expects(self::once())
            ->method('loadSnapshot')
            ->with(EventSourcedUser::className(), $aggregateId);
        $repository->load($aggregateId);
    }

    /**
     * @test
     */
    public function snapshotShouldBeCreated()
    {
        $aggregateId = new UUIDAggregateId(UUID::randomUUID());
        $aggregateRoot = $this->getMock(
            EventSourcedUser::className(),
            ['getId', 'getAndClearRaisedEvents'],
            [],
            '',
            false
        );
        $aggregateRoot
            ->expects(self::any())
            ->method('getId')
            ->will(self::returnValue($aggregateId));

        $events = new ArrayIterator([new IncrementedEvent($aggregateId, 1)]);

        $aggregateRoot
            ->expects(self::once())
            ->method('getAndClearRaisedEvents')
            ->will(self::returnValue($events));

        $eventStore = $this->getMock('\predaddy\domain\SnapshotEventStore');
        $eventStore
            ->expects(self::once())
            ->method('saveChanges');

        $repository = new EventSourcingRepository(EventSourcedUser::objectClass(), $this->eventBus, $eventStore);
        $eventStore
            ->expects(self::once())
            ->method('createSnapshot')
            ->with(EventSourcedUser::className(), $aggregateId);
        $repository->save($aggregateRoot, 1);
    }

    public function testSetVersionAndAggregateIdFields()
    {
        $createCommand = new CreateEventSourcedUser(null, 0);
        $user = new EventSourcedUser($createCommand);
        $userId = null;
        $version = null;
        $this->eventBus->registerClosure(
            function (UserCreated $event) use (&$userId, &$version) {
                $userId = $event->getAggregateId();
                $version = $event->getStateHash();
            }
        );
        $this->repository->save($user);
        self::assertNotNull($userId);
        self::assertEquals($version, $user->getStateHash());

        $this->eventBus->registerClosure(
            function (DecrementedEvent $event) use (&$userId, &$version) {
                $userId = $event->getAggregateId();
                $version = $event->getStateHash();
            }
        );
        $user->decrement(new Decrement());
        $this->repository->save($user);
        self::assertNotNull($userId);
        self::assertEquals($version, $user->getStateHash());
    }
}
