<?php
declare(strict_types=1);

namespace predaddy\domain\eventsourcing;

use precore\lang\BaseObject;
use precore\lang\ObjectClass;
use predaddy\domain\AggregateId;
use predaddy\domain\DomainEvent;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class AbstractSnapshotEventStore extends BaseObject implements SnapshotEventStore
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
    abstract protected function doCreateSnapshot(EventSourcedAggregateRoot $aggregateRoot) : void;

    /**
     * @param DomainEvent $event
     * @return int version number
     */
    abstract protected function doPersist(DomainEvent $event) : int;

    /**
     * @param DomainEvent $event
     * @return void
     */
    public function persist(DomainEvent $event) : void
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
    public function createSnapshot(AggregateId $aggregateId) : void
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

    private function eventSourced(AggregateId $aggregateId) : bool
    {
        $class = ObjectClass::forName($aggregateId->aggregateClass());
        return $class->isSubclassOf('predaddy\domain\eventsourcing\EventSourcedAggregateRoot');
    }
}
