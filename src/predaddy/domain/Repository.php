<?php
declare(strict_types=1);

namespace predaddy\domain;

/**
 * Repository interface for loading and persisting aggregates.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface Repository
{
    /**
     * Load the aggregate identified by $aggregateId from the persistent storage.
     *
     * @param AggregateId $aggregateId
     * @return AggregateRoot
     * @throws \InvalidArgumentException If the $aggregateId is invalid
     */
    public function load(AggregateId $aggregateId) : AggregateRoot;

    /**
     * Persisting the given $aggregateRoot.
     *
     * @param AggregateRoot $aggregateRoot
     */
    public function save(AggregateRoot $aggregateRoot) : void;
}
