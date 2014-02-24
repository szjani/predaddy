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

namespace predaddy\domain\impl\doctrine;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use predaddy\domain\AggregateId;
use predaddy\domain\EventSourcedAggregateRoot;
use predaddy\domain\EventSourcingRepository;

class DoctrineEventSourcingRepository extends EventSourcingRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param DoctrineOrmEventStorage $eventStorage
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(DoctrineOrmEventStorage $eventStorage, EntityManagerInterface $entityManager)
    {
        parent::__construct($eventStorage);
        $this->entityManager = $entityManager;
    }

    /**
     * @param AggregateId $aggregateId
     * @throws \InvalidArgumentException
     * @return EventSourcedAggregateRoot
     */
    protected function innerLoad(AggregateId $aggregateId)
    {
        $idValue = $aggregateId->getValue();
        /* @var $aggregate Aggregate */
        $aggregate = $this->entityManager->find(Aggregate::className(), $idValue);
        $this->checkAggregate($idValue, $aggregate);
        return $aggregate->getAggregateRoot();
    }

    /**
     * @param AggregateId $aggregateId
     * @throws \InvalidArgumentException
     * @return int
     */
    protected function obtainVersion(AggregateId $aggregateId)
    {
        $idValue = $aggregateId->getValue();
        /* @var $aggregate Aggregate */
        $aggregate = $this->entityManager->find(Aggregate::className(), $idValue);
        $this->checkAggregate($idValue, $aggregate);
        return $aggregate->getVersion();
    }

    protected function checkAggregate($idValue, Aggregate $aggregate = null)
    {
        if ($aggregate === null) {
            throw new InvalidArgumentException(sprintf("The given aggregate ID [%s] is invalid!", $idValue));
        }
    }
}
