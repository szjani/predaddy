<?php
declare(strict_types=1);

namespace predaddy\domain\eventsourcing;

use InvalidArgumentException;
use precore\lang\BaseObject;
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
class EventSourcingRepository extends BaseObject implements Repository
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
    public function getEventStore() : EventStore
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
    public function load(AggregateId $aggregateId) : AggregateRoot
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
    public function save(AggregateRoot $aggregateRoot) : void
    {
    }
}
