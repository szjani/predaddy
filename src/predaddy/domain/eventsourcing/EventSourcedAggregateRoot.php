<?php
declare(strict_types=1);

namespace predaddy\domain\eventsourcing;

use Iterator;
use predaddy\domain\AggregateRoot;

/**
 * Interface for aggregate roots in an event sourcing architecture.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface EventSourcedAggregateRoot extends AggregateRoot
{
    /**
     * Initialize the aggregate from the given events.
     *
     * @see EventSourcingRepository
     * @param Iterator $events DomainEvent iterator
     */
    public function loadFromHistory(Iterator $events) : void;
}
