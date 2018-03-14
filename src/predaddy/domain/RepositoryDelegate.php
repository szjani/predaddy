<?php
declare(strict_types=1);

namespace predaddy\domain;

use InvalidArgumentException;
use OutOfBoundsException;
use precore\util\Preconditions;

/**
 * Delegates method calls to the repository registered to the specified aggregate.
 *
 * @package predaddy\domain
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class RepositoryDelegate implements Repository
{
    /**
     * @var array
     */
    private $repositories;

    /**
     * Keys are the aggregate class name, the values are the corresponding repository.
     *
     * @param array $repositories
     */
    public function __construct(array $repositories)
    {
        $this->repositories = $repositories;
    }

    /**
     * Load the aggregate identified by $aggregateId from the persistent storage.
     *
     * @param AggregateId $aggregateId
     * @return AggregateRoot
     * @throws InvalidArgumentException If the $aggregateId is invalid
     */
    public function load(AggregateId $aggregateId) : AggregateRoot
    {
        return $this->getProperRepository($aggregateId->aggregateClass())->load($aggregateId);
    }

    /**
     * Persisting the given $aggregateRoot.
     *
     * @param AggregateRoot $aggregateRoot
     */
    public function save(AggregateRoot $aggregateRoot) : void
    {
        $this->getProperRepository($aggregateRoot->className())->save($aggregateRoot);
    }

    /**
     * @param $className
     * @return Repository
     * @throws InvalidArgumentException
     */
    private function getProperRepository($className) : Repository
    {
        try {
            return Preconditions::checkElementExists(
                $this->repositories,
                $className,
                "There is no registered repository to aggregate '%s'",
                $className
            );
        } catch (OutOfBoundsException $e) {
            throw new InvalidArgumentException('', 0, $e);
        }
    }
}
