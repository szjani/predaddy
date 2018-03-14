<?php
declare(strict_types=1);

namespace predaddy\inmemory;

use ArrayIterator;
use Countable;
use Iterator;
use predaddy\domain\AggregateId;
use predaddy\domain\DomainEvent;
use predaddy\domain\eventsourcing\AbstractSnapshotEventStore;
use predaddy\domain\eventsourcing\EventSourcedAggregateRoot;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class InMemoryEventStore extends AbstractSnapshotEventStore
{
    private $events = [];
    private $snapshots = [];

    /**
     * @param EventSourcedAggregateRoot $aggregateRoot
     * @return void
     */
    protected function doCreateSnapshot(EventSourcedAggregateRoot $aggregateRoot) : void
    {
        $this->snapshots[$this->createKey($aggregateRoot->getId())] = $aggregateRoot;
    }

    /**
     * @param DomainEvent $event
     * @return int version number
     */
    protected function doPersist(DomainEvent $event) : int
    {
        self::getLogger()->debug('Persisting event into inmemory event store [{}]...', [$event]);
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
    public function getEventsFor(AggregateId $aggregateId, string $stateHash = null)
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
        self::getLogger()->debug(
            'Events for aggregate [{}] with state hash [{}] has been loaded',
            [$aggregateId, $stateHash]
        );
        return new ArrayIterator($result);
    }

    /**
     * @param AggregateId $aggregateId
     * @return EventSourcedAggregateRoot|null
     */
    public function loadSnapshot(AggregateId $aggregateId) : ?EventSourcedAggregateRoot
    {
        self::getLogger()->debug('Loading snapshot for aggregate [{}]...', [$aggregateId]);
        $key = $this->createKey($aggregateId);
        return array_key_exists($key, $this->snapshots)
            ? $this->snapshots[$key]
            : null;
    }

    public function clean() : void
    {
        $this->events = [];
        $this->snapshots = [];
        self::getLogger()->debug('Inmemory event store has been cleared');
    }

    private function createKey(AggregateId $aggregateId) : string
    {
        return $aggregateId->aggregateClass() . $aggregateId->value();
    }
}
