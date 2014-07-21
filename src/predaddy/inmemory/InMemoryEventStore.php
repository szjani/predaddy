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

namespace predaddy\inmemory;

use ArrayIterator;
use Countable;
use Iterator;
use predaddy\domain\AggregateId;
use predaddy\domain\DomainEvent;
use predaddy\domain\eventsourcing\AbstractSnapshotEventStore;
use predaddy\domain\eventsourcing\EventSourcedAggregateRoot;

/**
 * @package predaddy\domain\impl
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
final class InMemoryEventStore extends AbstractSnapshotEventStore
{
    private $events = [];
    private $snapshots = [];

    /**
     * @param EventSourcedAggregateRoot $aggregateRoot
     * @return void
     */
    protected function doCreateSnapshot(EventSourcedAggregateRoot $aggregateRoot)
    {
        $this->snapshots[$this->createKey($aggregateRoot->getId())] = $aggregateRoot;
    }

    /**
     * @param DomainEvent $event
     * @return int version number
     */
    protected function doPersist(DomainEvent $event)
    {
        $key = $this->createKey($event->aggregateId());
        $this->events[$key][] = $event;
        return count($this->events[$key]);
    }

    /**
     * Must be return all events stored to aggregate identified by $aggregateId and $type.
     * Events must be ordered by theirs persistent time.
     *
     * If the $stateHash parameter is set, the result will contain only the newer DomainEvents.
     *
     * @param AggregateId $aggregateId
     * @param string $stateHash State hash
     * @return Iterator|Countable
     */
    public function getEventsFor(AggregateId $aggregateId, $stateHash = null)
    {
        $result = [];
        $add = false;
        $key = $this->createKey($aggregateId);
        $events = array_key_exists($key, $this->events) ? $this->events[$key] : [];
        /* @var $event DomainEvent */
        foreach ($events as $event) {
            if ($add || $stateHash === null) {
                $result[] = $event;
            } elseif ($event->stateHash() === $stateHash) {
                $add = true;
            }
        }
        return new ArrayIterator($result);
    }

    /**
     * @param AggregateId $aggregateId
     * @return EventSourcedAggregateRoot|null
     */
    public function loadSnapshot(AggregateId $aggregateId)
    {
        $key = $this->createKey($aggregateId);
        return array_key_exists($key, $this->snapshots)
            ? $this->snapshots[$key]
            : null;
    }

    public function clean()
    {
        $this->events = [];
        $this->snapshots = [];
    }

    private function createKey(AggregateId $aggregateId)
    {
        return $aggregateId->aggregateClass() . $aggregateId->value();
    }
}
