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

use ArrayIterator;
use Countable;
use Iterator;
use predaddy\domain\AbstractSnapshotEventStore;
use predaddy\domain\AggregateId;
use predaddy\domain\DomainEvent;
use predaddy\domain\EventSourcedAggregateRoot;

/**
 * @package predaddy\domain\impl
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class InMemoryEventStore extends AbstractSnapshotEventStore
{
    private $events = [];
    private $snapshots = [];

    /**
     * @param EventSourcedAggregateRoot $aggregateRoot
     * @return void
     */
    protected function doCreateSnapshot(EventSourcedAggregateRoot $aggregateRoot)
    {
        $this->snapshots[$this->createKey($aggregateRoot->getClassName(), $aggregateRoot->getId())] = $aggregateRoot;
    }

    /**
     * @param DomainEvent $event
     * @return int version number
     */
    protected function doPersist(DomainEvent $event)
    {
        $key = $this->createKey($event->getAggregateClass(), $event->getAggregateId());
        $this->events[$key][] = $event;
        return count($this->events[$key]);
    }

    /**
     * Must be return all events stored to aggregate identified by $aggregateId and $type.
     * Events must be ordered by theirs persistent time.
     *
     * If the $stateHash parameter is set, the result will contain only the newer DomainEvents.
     *
     * @param string $aggregateRootClass FQCN
     * @param AggregateId $aggregateId
     * @param string $stateHash State hash
     * @return Iterator|Countable
     */
    public function getEventsFor($aggregateRootClass, AggregateId $aggregateId, $stateHash = null)
    {
        $result = [];
        $add = false;
        /* @var $event DomainEvent */
        foreach ($this->events[$this->createKey($aggregateRootClass, $aggregateId)] as $event) {
            if ($add || $stateHash === null) {
                $result[] = $event;
            } elseif ($event->getStateHash() === $stateHash) {
                $add = true;
            }
        }
        return new ArrayIterator($result);
    }

    /**
     * @param string $aggregateRootClass FQCN
     * @param AggregateId $aggregateId
     * @return EventSourcedAggregateRoot|null
     */
    public function loadSnapshot($aggregateRootClass, AggregateId $aggregateId)
    {
        $key = $this->createKey($aggregateRootClass, $aggregateId);
        return array_key_exists($key, $this->snapshots)
            ? $this->snapshots[$key]
            : null;
    }

    public function clean()
    {
        $this->events = [];
        $this->snapshots = [];
    }

    private function createKey($aggregateRootClass, AggregateId $aggregateId)
    {
        return $aggregateRootClass . $aggregateId->getValue();
    }
}
