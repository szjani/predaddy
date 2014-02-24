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

use ArrayIterator;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Iterator;
use predaddy\domain\AggregateId;
use predaddy\domain\EventSourcedAggregateRoot;
use predaddy\domain\EventStorage;
use predaddy\domain\TrivialSnapshotStrategy;
use predaddy\domain\SnapshotStrategy;

class DoctrineOrmEventStorage implements EventStorage
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SnapshotStrategy
     */
    private $snapshotStrategy;

    /**
     * @param EntityManagerInterface $entityManager
     * @param SnapshotStrategy $snapshotStrategy
     */
    public function __construct(EntityManagerInterface $entityManager, SnapshotStrategy $snapshotStrategy = null)
    {
        $this->entityManager = $entityManager;
        if ($snapshotStrategy === null) {
            $snapshotStrategy = TrivialSnapshotStrategy::$NEVER;
        }
        $this->snapshotStrategy = $snapshotStrategy;
    }

    /**
     * @param AggregateId $aggregateId
     * @param Iterator $events
     * @param $originatingVersion
     * @param EventSourcedAggregateRoot $aggregateRoot
     * @return void
     */
    public function saveChanges(
        AggregateId $aggregateId,
        Iterator $events,
        $originatingVersion,
        EventSourcedAggregateRoot $aggregateRoot = null
    ) {
        $aggregate = $this->entityManager->find(Aggregate::className(), $aggregateId);
        if ($aggregate === null) {
            $aggregate = new Aggregate($aggregateId, $aggregateRoot->getClassName());
            $this->entityManager->persist($aggregate);
        }
        $this->entityManager->lock($aggregate, LockMode::OPTIMISTIC, $originatingVersion);
        foreach ($events as $event) {
            $aggregate->increaseVersion();
            $this->entityManager->persist(new Event($event, $aggregateId, $aggregate->getVersion()));
        }
        if ($this->snapshotStrategy->snapshotRequired($aggregateRoot, $originatingVersion, $aggregate->getVersion())) {
            $aggregate->setAggregateRoot($aggregateRoot);
        }
    }

    /**
     * @param AggregateId $aggregateId
     * @param int $fromVersion
     * @return Iterator
     */
    public function getEventsFor(AggregateId $aggregateId, $fromVersion = 0)
    {
        $events = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(Event::className(), 'e')
            ->where('e.aggregateId = :aggregateId AND e.version > :fromVersion')
            ->orderBy('e.version')
            ->getQuery()
            ->execute(
                array(
                    'aggregateId' => $aggregateId->getValue(),
                    'fromVersion' => $fromVersion
                )
            );
        $result = array();
        /* @var $event Event */
        foreach ($events as $event) {
            $result[] = $event->getData();
        }
        return new ArrayIterator($result);
    }
}
