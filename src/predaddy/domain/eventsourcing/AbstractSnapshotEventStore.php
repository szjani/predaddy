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

use precore\lang\Object;
use precore\lang\ObjectClass;
use predaddy\domain\AggregateId;
use predaddy\domain\DomainEvent;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class AbstractSnapshotEventStore extends Object implements SnapshotEventStore
{
    /**
     * @var SnapshotStrategy
     */
    private $snapshotStrategy;

    public function __construct(SnapshotStrategy $snapshotStrategy = null)
    {
        if ($snapshotStrategy === null) {
            $snapshotStrategy = TrivialSnapshotStrategy::$NEVER;
        }
        $this->snapshotStrategy = $snapshotStrategy;
    }

    /**
     * @param EventSourcedAggregateRoot $aggregateRoot
     * @return void
     */
    abstract protected function doCreateSnapshot(EventSourcedAggregateRoot $aggregateRoot);

    /**
     * @param DomainEvent $event
     * @return int version number
     */
    abstract protected function doPersist(DomainEvent $event);

    /**
     * @param DomainEvent $event
     * @return void
     */
    public function persist(DomainEvent $event)
    {
        $version = $this->doPersist($event);
        $aggregateId = $event->aggregateId();
        if ($this->eventSourced($aggregateId) && $this->snapshotStrategy->snapshotRequired($event, $version)) {
            $this->createSnapshot($aggregateId);
        }
    }

    /**
     * Events raised in the current transaction are not being stored in this snapshot.
     * Only the already persisted events are being utilized.
     *
     * @param AggregateId $aggregateId
     */
    public function createSnapshot(AggregateId $aggregateId)
    {
        if (!$this->eventSourced($aggregateId)) {
            return;
        }
        /* @var $aggregateRoot EventSourcedAggregateRoot */
        $aggregateRoot = $this->loadSnapshot($aggregateId);
        $stateHash = $aggregateRoot === null ? null : $aggregateRoot->stateHash();
        $events = $this->getEventsFor($aggregateId, $stateHash);
        if ($events->count() == 0) {
            return;
        }
        if ($aggregateRoot === null) {
            $aggregateRoot = ObjectClass::forName($aggregateId->aggregateClass())->newInstanceWithoutConstructor();
        }
        $aggregateRoot->loadFromHistory($events);
        $this->doCreateSnapshot($aggregateRoot);
    }

    private function eventSourced(AggregateId $aggregateId)
    {
        $class = ObjectClass::forName($aggregateId->aggregateClass());
        return $class->isSubclassOf('predaddy\domain\eventsourcing\EventSourcedAggregateRoot');
    }
}
