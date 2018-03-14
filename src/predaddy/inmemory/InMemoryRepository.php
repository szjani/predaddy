<?php
declare(strict_types=1);

namespace predaddy\inmemory;

use precore\lang\BaseObject;
use precore\util\Preconditions;
use predaddy\domain\AggregateId;
use predaddy\domain\AggregateRoot;
use predaddy\domain\Repository;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class InMemoryRepository extends BaseObject implements Repository
{
    /**
     * @var array
     */
    private $aggregates = [];

    /**
     * Load the aggregate identified by $aggregateId from the persistent storage.
     *
     * @param AggregateId $aggregateId
     * @return AggregateRoot
     * @throws \InvalidArgumentException If the $aggregateId is invalid
     */
    public function load(AggregateId $aggregateId) : AggregateRoot
    {
        $key = $this->createKey($aggregateId);
        Preconditions::checkArgument(
            array_key_exists($key, $this->aggregates),
            'Aggregate with ID [%s] does not exist',
            $aggregateId
        );
        self::getLogger()->debug('Aggregate identified by [{}] has been loaded', [$aggregateId]);
        return $this->aggregates[$key];
    }

    /**
     * Persisting the given $aggregateRoot.
     *
     * @param AggregateRoot $aggregateRoot
     */
    public function save(AggregateRoot $aggregateRoot) : void
    {
        $this->aggregates[$this->createKey($aggregateRoot->getId())] = $aggregateRoot;
        self::getLogger()->debug('Aggregate identified by [{}] has been persisted', [$aggregateRoot->getId()]);
    }

    private function createKey(AggregateId $aggregateId) : string
    {
        return $aggregateId->aggregateClass() . $aggregateId->value();
    }
}
