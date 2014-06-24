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
use Doctrine\ORM\EntityManagerInterface;
use precore\lang\ObjectClass;
use predaddy\domain\AbstractSnapshotEventStore;
use predaddy\domain\AggregateId;
use predaddy\domain\DomainEvent;
use predaddy\domain\EventSourcedAggregateRoot;
use predaddy\domain\SnapshotEventStore;
use predaddy\domain\SnapshotStrategy;
use predaddy\serializer\PHPSerializer;
use predaddy\serializer\Serializer;

class DoctrineOrmEventStore extends AbstractSnapshotEventStore implements SnapshotEventStore
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param EntityManagerInterface $entityManager
     * @param SnapshotStrategy $snapshotStrategy
     * @param Serializer $serializer Used to serialize events and ARs for snapshotting
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SnapshotStrategy $snapshotStrategy = null,
        Serializer $serializer = null
    ) {
        parent::__construct($snapshotStrategy);
        $this->entityManager = $entityManager;
        if ($serializer === null) {
            $serializer = new PHPSerializer();
        }
        $this->serializer = $serializer;
    }

    /**
     * @param AggregateId $aggregateId
     * @param null $stateHash
     * @return ArrayIterator
     */
    public function getEventsFor(AggregateId $aggregateId, $stateHash = null)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(Event::className(), 'e')
            ->where('e.aggregateType = :type AND e.aggregateId = :aggregateId')
            ->setParameters(
                [
                    'type' => $aggregateId->aggregateClass(),
                    'aggregateId' => $aggregateId->value()
                ]
            );
        if ($stateHash !== null) {
            $queryBuilder->andWhere(
                sprintf(
                    'e.sequenceNumber > (
                        SELECT e2.sequenceNumber
                        FROM %s e2
                        WHERE e2.aggregateType = :type AND e2.aggregateId = :aggregateId AND e2.stateHash = :stateHash
                    )',
                    Event::className()
                )
            );
            $queryBuilder->setParameter('stateHash', $stateHash);
        }
        $events = $queryBuilder
            ->orderBy('e.sequenceNumber')
            ->getQuery()
            ->execute();
        $result = [];
        /* @var $event Event */
        foreach ($events as $event) {
            $result[] = $this->serializer->deserialize($event->getData(), ObjectClass::forName($event->getEventType()));
        }
        return new ArrayIterator($result);
    }

    /**
     * @param AggregateId $aggregateId
     * @return EventSourcedAggregateRoot|null
     */
    public function loadSnapshot(AggregateId $aggregateId)
    {
        $aggregateRoot = null;
        /* @var $snapshot Snapshot */
        $snapshot = $this->findSnapshot($aggregateId);
        if ($snapshot !== null) {
            $reflectionClass = ObjectClass::forName($aggregateId->aggregateClass());
            $aggregateRoot = $this->serializer->deserialize($snapshot->getAggregateRoot(), $reflectionClass);
            self::getLogger()->debug("Snapshot loaded [{}]", [$aggregateId]);
        }
        return $aggregateRoot;
    }

    /**
     * @param DomainEvent $event
     * @return int
     */
    protected function doPersist(DomainEvent $event)
    {
        /* @var $event DomainEvent */
        $aggregateId = $event->aggregateId();
        $aggregate = $this->findAggregate($aggregateId);
        if ($aggregate === null) {
            $aggregate = new Aggregate($aggregateId);
            $this->entityManager->persist($aggregate);
            self::getLogger()->debug("Aggregate has been persisted [{}]", [$aggregateId]);
        }
        $metaEvent = $aggregate->createMetaEvent($event, $this->serializer->serialize($event));
        $this->entityManager->persist($metaEvent);
        self::getLogger()->debug("Event has been persisted [{}]", [$metaEvent]);
        return $aggregate->getVersion();
    }

    protected function doCreateSnapshot(EventSourcedAggregateRoot $aggregateRoot)
    {
        $aggregateId = $aggregateRoot->getId();
        /* @var $snapshot Snapshot */
        $aggregate = $this->findAggregate($aggregateId);
        $snapshot = $this->findSnapshot($aggregateId);
        $serialized = $this->serializer->serialize($aggregateRoot);
        if ($snapshot === null) {
            $snapshot = $aggregate->createSnapshot($serialized);
            $this->entityManager->persist($snapshot);
        } else {
            $aggregate->updateSnapshot($snapshot, $serialized);
        }
        self::getLogger()->debug(
            "Snapshot has been persisted for aggregate [{}], version [{}]",
            [$aggregateId, $aggregate->getVersion()]
        );
    }

    /**
     * @param AggregateId $aggregateId
     * @return Aggregate|null
     */
    protected function findAggregate(AggregateId $aggregateId)
    {
        return $this->entityManager->find(
            Aggregate::className(),
            Aggregate::createPrimaryIdArray($aggregateId)
        );
    }

    /**
     * @param AggregateId $aggregateId
     * @return Snapshot|null
     */
    protected function findSnapshot(AggregateId $aggregateId)
    {
        return $this->entityManager->find(
            Snapshot::className(),
            Snapshot::createPrimaryIdArray($aggregateId)
        );
    }
}
