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

abstract class EventSourcingRepository
{
    /**
     * @var EventStorage
     */
    private $eventStorage;

    /**
     * @param EventStorage $eventStorage
     */
    public function __construct(EventStorage $eventStorage)
    {
        $this->eventStorage = $eventStorage;
    }

    /**
     * Load the aggregate from the persistent storage by the given $aggregateId.
     * In case of snapshotting it should be unserialized directly,
     * otherwise it should be created with calling newInstanceWithoutConstructor() on its class.
     *
     * @param AggregateId $aggregateId
     * @return EventSourcedAggregateRoot
     * @throws \InvalidArgumentException If the $aggregateId is invalid
     */
    abstract protected function innerLoad(AggregateId $aggregateId);

    /**
     * @param AggregateId $aggregateId
     * @return int
     * @throws \InvalidArgumentException If the $aggregateId is invalid
     */
    abstract protected function obtainVersion(AggregateId $aggregateId);

    /**
     * @return EventStorage
     */
    public function getEventStorage()
    {
        return $this->eventStorage;
    }

    /**
     * Initializes the stored aggregate with its events persisted in event store.
     *
     * @param AggregateId $aggregateId
     * @return EventSourcedAggregateRoot
     * @throws \InvalidArgumentException If the $aggregateId is invalid
     */
    public function load(AggregateId $aggregateId)
    {
        $aggregate = $this->innerLoad($aggregateId);
        $events = $this->eventStorage->getEventsFor($aggregateId, $this->obtainVersion($aggregateId));
        $aggregate->loadFromHistory($events);
        return $aggregate;
    }

    /**
     * @param EventSourcedAggregateRoot $aggregateRoot
     * @param int $version
     */
    public function save(EventSourcedAggregateRoot $aggregateRoot, $version)
    {
        $newEvents = $aggregateRoot->getAndClearRaisedEvents();
        $this->eventStorage->saveChanges($aggregateRoot->getId(), $newEvents, $version, $aggregateRoot);
    }
}
