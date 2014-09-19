<?php
/*
 * Copyright (c) 2012-2014 Szurovecz János
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

namespace predaddy\inmemory;

use predaddy\domain\AbstractRepository;
use predaddy\domain\AggregateId;
use predaddy\domain\AggregateRoot;

/**
 * @author Szurovecz János <szjani@szjani.hu>
 */
final class InMemoryRepository extends AbstractRepository
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
    public function load(AggregateId $aggregateId)
    {
        $key = $this->createKey($aggregateId);
        if (!array_key_exists($key, $this->aggregates)) {
            $this->throwInvalidAggregateIdException($aggregateId);
        }
        self::getLogger()->debug('Aggregate identified by [{}] has been loaded', [$aggregateId]);
        return $this->aggregates[$key];
    }

    /**
     * Persisting the given $aggregateRoot.
     *
     * @param AggregateRoot $aggregateRoot
     */
    public function save(AggregateRoot $aggregateRoot)
    {
        $this->aggregates[$this->createKey($aggregateRoot->getId())] = $aggregateRoot;
        self::getLogger()->debug('Aggregate identified by [{}] has been persisted', [$aggregateRoot->getId()]);
    }

    private function createKey(AggregateId $aggregateId)
    {
        return $aggregateId->aggregateClass() . $aggregateId->value();
    }
}
