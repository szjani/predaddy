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
use PHPUnit_Framework_TestCase;
use precore\util\UUID;

class EventSourcingRepositoryTest extends PHPUnit_Framework_TestCase
{
    private $eventStorage;

    /**
     * @var EventSourcingRepository
     */
    private $repository;

    public function setUp()
    {
        $this->eventStorage = $this->getMock('\predaddy\domain\EventStorage');
        $this->repository = $this->getMock(
            '\predaddy\domain\EventSourcingRepository',
            array('innerLoad', 'obtainVersion'),
            array($this->eventStorage)
        );
    }

    public function testGetEventStorage()
    {
        self::assertSame($this->eventStorage, $this->repository->getEventStorage());
    }

    public function testLoad()
    {
        $aggregateId = new UUIDAggregateId(UUID::randomUUID());

        $aggregate = $this->getMock('\predaddy\domain\EventSourcedAggregateRoot', array('loadFromHistory', 'getId'));
        $version = 2;

        $this->repository
            ->expects(self::once())
            ->method('innerLoad')
            ->with($aggregateId)
            ->will(self::returnValue($aggregate));

        $this->repository
            ->expects(self::once())
            ->method('obtainVersion')
            ->with($aggregateId)
            ->will(self::returnValue(2));

        $event1 = $this->getMock('\predaddy\domain\DomainEvent', array(), array(), '', false);
        $event2 = $this->getMock('\predaddy\domain\DomainEvent', array(), array(), '', false);
        $events = new ArrayIterator(array($event1, $event2));
        $this->eventStorage
            ->expects(self::once())
            ->method('getEventsFor')
            ->with($aggregateId, $version)
            ->will(self::returnValue($events));

        $aggregate
            ->expects(self::once())
            ->method('loadFromHistory')
            ->with($events);

        $this->repository->load($aggregateId);
    }

    public function testSave()
    {
        $aggregateId = new UUIDAggregateId(UUID::randomUUID());
        $version = 3;
        $event1 = $this->getMock('\predaddy\domain\DomainEvent', array(), array(), '', false);
        $event2 = $this->getMock('\predaddy\domain\DomainEvent', array(), array(), '', false);
        $events = new ArrayIterator(array($event1, $event2));

        $aggregate = $this->getMock(
            '\predaddy\domain\EventSourcedAggregateRoot',
            array('getAndClearRaisedEvents', 'getId')
        );

        $aggregate
            ->expects(self::once())
            ->method('getId')
            ->will(self::returnValue($aggregateId));

        $aggregate
            ->expects(self::once())
            ->method('getAndClearRaisedEvents')
            ->will(self::returnValue($events));

        $this->eventStorage
            ->expects(self::once())
            ->method('saveChanges')
            ->with($aggregateId, $events, $version, $aggregate);

        $this->repository->save($aggregate, $version);
    }
}
