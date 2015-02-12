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

namespace predaddy\util\test;

use precore\util\UUID;
use predaddy\domain\AbstractDomainEvent;
use predaddy\domain\GenericAggregateId;
use predaddy\domain\DomainEvent;
use predaddy\domain\eventsourcing\EventSourcingRepository;
use predaddy\domain\EventStore;
use predaddy\domain\NullAggregateId;
use predaddy\inmemory\InMemoryEventStore;

/**
 * Class EventSourcedFixture
 *
 * @package predaddy\util\test
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class EventSourcedFixture extends Fixture
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var DomainEvent[]
     */
    private $given = [];

    public function __construct($aggregateClass)
    {
        $this->eventStore = new InMemoryEventStore();
        parent::__construct($aggregateClass, new EventSourcingRepository($this->eventStore));
    }

    /**
     * These events will be initialize the AR. It works only with ES aggregates.
     *
     * @param DomainEvent $events
     * @return EventSourcedFixture
     */
    public function givenEvents(DomainEvent $events)
    {
        $this->given = func_get_args();
        foreach ($this->given as $event) {
            if ($this->getAggregateId() === null) {
                $aggregateId = $event->aggregateId() !== null && !$event->aggregateId()->equals(NullAggregateId::instance())
                    ? $event->aggregateId()
                    : new GenericAggregateId(UUID::randomUUID()->toString(), $this->getAggregateClass());
                $this->setAggregateId($aggregateId);
            }
            if ($event instanceof AbstractDomainEvent) {
                AbstractDomainEvent::initEvent($event, $this->getAggregateId(), $event->identifier());
            }
            $this->eventStore->persist($event);
        }
        return $this;
    }
}
