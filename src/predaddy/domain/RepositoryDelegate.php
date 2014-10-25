<?php
/*
 * Copyright (c) 2012-2014 Janos Szurovecz
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
    public function load(AggregateId $aggregateId)
    {
        return $this->getProperRepository($aggregateId->aggregateClass())->load($aggregateId);
    }

    /**
     * Persisting the given $aggregateRoot.
     *
     * @param AggregateRoot $aggregateRoot
     */
    public function save(AggregateRoot $aggregateRoot)
    {
        $this->getProperRepository($aggregateRoot->className())->save($aggregateRoot);
    }

    /**
     * @param $className
     * @return Repository
     * @throws InvalidArgumentException
     */
    private function getProperRepository($className)
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
