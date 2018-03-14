<?php
declare(strict_types=1);

namespace predaddy\domain;

use predaddy\eventhandling\Event;

/**
 * Interface for domain events. It must know the identifier of the aggregate
 * which belongs to and can hold the state hash of the aggregate.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface DomainEvent extends Event, StateHashAware
{
    /**
     * @return AggregateId
     */
    public function aggregateId() : AggregateId;
}
