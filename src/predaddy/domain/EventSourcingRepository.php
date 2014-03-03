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

use InvalidArgumentException;
use Iterator;
use precore\lang\ObjectClass;
use predaddy\eventhandling\EventBus;

/**
 * Should be used for event sourcing.
 *
 * @package predaddy\domain
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class EventSourcingRepository extends AggregateRootRepository
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var ObjectClass
     */
    private $aggregateRootClass;

    /**
     * @var SnapshotStrategy
     */
    private $snapshotStrategy;

    /**
     * @param ObjectClass $aggregateRootClass Must be an EventSourcedAggregateRoot type
     * @param EventBus $eventBus
     * @param EventStore $eventStore
     * @param SnapshotStrategy $snapshotStrategy
     */
    public function __construct(
        ObjectClass $aggregateRootClass,
        EventBus $eventBus,
        EventStore $eventStore,
        SnapshotStrategy $snapshotStrategy = null
    ) {
        parent::__construct($eventBus);
        $this->eventStore = $eventStore;
        $this->aggregateRootClass = $aggregateRootClass;
        if ($snapshotStrategy === null) {
            $snapshotStrategy = TrivialSnapshotStrategy::$ALWAYS;
        }
        $this->snapshotStrategy = $snapshotStrategy;
    }

    /**
     * @return ObjectClass
     */
    public function getAggregateRootClass()
    {
        return $this->aggregateRootClass;
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
        $events = $this->eventStore->getEventsFor($this->aggregateRootClass->getName(), $aggregateId);
        $aggregate = null;
        if ($this->eventStore instanceof SnapshotEventStore) {
            $aggregate = $this->eventStore->loadSnapshot($this->aggregateRootClass->getName(), $aggregateId);
        }
        if ($aggregate === null) {
            if (!$events->valid()) {
                $this->throwInvalidAggregateIdException($aggregateId);
            }
            $aggregate = $this->aggregateRootClass->newInstanceWithoutConstructor();
        }
        $this->aggregateRootClass->cast($aggregate);
        $aggregate->loadFromHistory($events);
        return $aggregate;
    }

    protected function innerSave(AggregateRoot $aggregateRoot, Iterator $events, $version)
    {
        $this->aggregateRootClass->cast($aggregateRoot);
        $this->eventStore->saveChanges($this->aggregateRootClass->getName(), $events, $version);
        if ($this->eventStore instanceof SnapshotEventStore
            && $this->snapshotStrategy->snapshotRequired($aggregateRoot, $version)) {

            $this->eventStore->createSnapshot($this->aggregateRootClass->getName(), $aggregateRoot->getId());
        }
    }

    protected function throwInvalidAggregateIdException(AggregateId $aggregateId)
    {
        throw new InvalidArgumentException(
            sprintf(
                "Aggregate [%s] with ID [%s] does not exist",
                $this->aggregateRootClass->getName(),
                $aggregateId->getValue()
            )
        );
    }
}
