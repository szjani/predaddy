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

use InvalidArgumentException;
use precore\lang\Object;
use precore\lang\ObjectClass;
use precore\util\Preconditions;
use predaddy\domain\AggregateId;
use predaddy\domain\AggregateRoot;
use predaddy\domain\EventStore;
use predaddy\domain\Repository;

/**
 * Should be used for event sourcing.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class EventSourcingRepository extends Object implements Repository
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @param EventStore $eventStore
     */
    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * @return EventStore
     */
    public function getEventStore()
    {
        return $this->eventStore;
    }

    /**
     * Initializes the stored aggregate with its events persisted in event store.
     *
     * @param AggregateId $aggregateId
     * @return EventSourcedAggregateRoot
     * @throws InvalidArgumentException If the $aggregateId is invalid
     */
    public function load(AggregateId $aggregateId)
    {
        $aggregateRootClass = ObjectClass::forName($aggregateId->aggregateClass());
        $aggregate = null;
        $stateHash = null;
        if ($this->eventStore instanceof SnapshotEventStore) {
            $aggregate = $this->eventStore->loadSnapshot($aggregateId);
            $stateHash = $aggregate === null
                ? null
                : $aggregate->stateHash();
        }
        $events = $this->eventStore->getEventsFor($aggregateId, $stateHash);
        if ($aggregate === null) {
            Preconditions::checkArgument($events->valid(), 'Aggregate with ID [%s] does not exist', $aggregateId);
            $aggregate = $aggregateRootClass->newInstanceWithoutConstructor();
        }
        $aggregateRootClass->cast($aggregate);
        $aggregate->loadFromHistory($events);
        return $aggregate;
    }

    /**
     * Persisting the given $aggregateRoot.
     *
     * @param AggregateRoot $aggregateRoot
     */
    public function save(AggregateRoot $aggregateRoot)
    {
    }
}
