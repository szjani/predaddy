<?php
declare(strict_types=1);

namespace predaddy\domain\eventsourcing;

use predaddy\domain\DomainEvent;

/**
 * Can be used in EventStore to decide whether the current version of the aggregate
 * should be persisted or not. The result actually is being a rolling snapshot.
 *
 * @see TrivialSnapshotStrategy
 */
interface SnapshotStrategy
{
    /**
     * @param DomainEvent $event
     * @param int|null $originalVersion
     * @return boolean
     */
    public function snapshotRequired(DomainEvent $event, ?int $originalVersion) : bool;
}
