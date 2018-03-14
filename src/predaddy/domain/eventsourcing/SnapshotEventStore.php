<?php
declare(strict_types=1);

namespace predaddy\domain\eventsourcing;

use predaddy\domain\AggregateId;
use predaddy\domain\EventStore;

/**
 * Interface SnapshotEventStore
 *
 * SnapshotEventStore.getEventsFor() method must returns all events raised since the last snapshot has been created.
 */
interface SnapshotEventStore extends EventStore
{
    /**
     * @param AggregateId $aggregateId
     * @return void
     */
    public function createSnapshot(AggregateId $aggregateId) : void;

    /**
     * @param AggregateId $aggregateId
     * @return EventSourcedAggregateRoot|null
     */
    public function loadSnapshot(AggregateId $aggregateId) : ?EventSourcedAggregateRoot;
}
