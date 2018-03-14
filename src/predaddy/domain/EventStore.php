<?php
declare(strict_types=1);

namespace predaddy\domain;

use Countable;
use Iterator;

/**
 * Responsible for managing event persistence.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface EventStore
{
    /**
     * Persists the given $event object.
     *
     * @param DomainEvent $event
     * @return void
     */
    public function persist(DomainEvent $event) : void;

    /**
     * Must return all events stored to the aggregate identified by $aggregateId.
     * Events must be ordered by theirs persistent time.
     *
     * If the $stateHash parameter is set, the result will contain only the newer DomainEvents.
     *
     * @param AggregateId $aggregateId
     * @param string $stateHash State hash
     * @return Iterator|Countable
     */
    public function getEventsFor(AggregateId $aggregateId, string $stateHash = null);
}
