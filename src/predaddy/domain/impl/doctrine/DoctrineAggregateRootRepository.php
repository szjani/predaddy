<?php
/*
 * Copyright (c) 2014 Szurovecz János
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

namespace predaddy\domain\impl\doctrine;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use precore\lang\ObjectClass;
use predaddy\domain\AggregateId;
use predaddy\domain\AggregateRoot;
use predaddy\domain\ClassBasedAggregateRootRepository;

/**
 * Generic repository class based on Doctrine ORM.
 * Should be used as a base class for repositories.
 *
 * @package predaddy\domain\impl\doctrine
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class DoctrineAggregateRootRepository extends ClassBasedAggregateRootRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param ObjectClass $aggregateRootClass
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        ObjectClass $aggregateRootClass,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($aggregateRootClass);
        $this->entityManager = $entityManager;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->entityManager;
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
        $result = $this->getEntityManager()->find($this->getAggregateRootClass()->getName(), $aggregateId->value());
        if ($result === null) {
            $this->throwInvalidAggregateIdException($aggregateId);
        }
        return $result;
    }

    /**
     * @param AggregateRoot $aggregateRoot
     * @return void
     */
    public function save(AggregateRoot $aggregateRoot)
    {
        parent::save($aggregateRoot);
        $entityManager = $this->getEntityManager();
        if (!$entityManager->contains($aggregateRoot)) {
            $entityManager->persist($aggregateRoot);
        }
    }
}
