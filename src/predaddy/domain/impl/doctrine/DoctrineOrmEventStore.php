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
use precore\lang\ObjectClass;
use predaddy\domain\AggregateId;
use predaddy\domain\DomainEvent;
use predaddy\domain\EventSourcedAggregateRoot;
use predaddy\domain\SnapshotEventStore;
use predaddy\Serializer;

class DoctrineOrmEventStore implements SnapshotEventStore
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
     * @param Serializer $serializer Used to serialize ARs for snapshotting
     */
    public function __construct(EntityManagerInterface $entityManager, Serializer $serializer = null)
    {
        $this->entityManager = $entityManager;
        if ($serializer === null) {
            $serializer = EventSourcedAggregateRoot::getSerializer();
        }
        $this->serializer = $serializer;
    }

    /**
     * @param string $aggregateRootClass
     * @param Iterator $events
     * @param $originatingVersion
     * @return void
     */
    public function saveChanges($aggregateRootClass, Iterator $events, $originatingVersion)
    {
        $aggregate = null;
        /* @var $event DomainEvent */
        foreach ($events as $event) {
            if ($aggregate === null) {
                $aggregateId = $event->getAggregateIdentifier();
                $aggregate = $this->findAggregate($aggregateRootClass, $aggregateId);
                if ($aggregate === null) {
                    $aggregate = new Aggregate($aggregateId, $aggregateRootClass);
                    $this->entityManager->persist($aggregate);
                }
                $this->entityManager->lock($aggregate, LockMode::OPTIMISTIC, $originatingVersion);
            }
            $metaEvent = new Event(
                $event->getAggregateIdentifier(),
                $aggregateRootClass,
                $aggregate->getVersion() + 1,
                $event->getTimestamp(),
                serialize($event)
            );
            $this->entityManager->persist($metaEvent);
        }
    }

    /**
     * @param string $aggregateRootClass
     * @param AggregateId $aggregateId
     * @return ArrayIterator
     */
    public function getEventsFor($aggregateRootClass, AggregateId $aggregateId)
    {
        /* @var $snapshot Snapshot */
        $snapshot = $this->findSnapshot($aggregateRootClass, $aggregateId);
        $fromVersion = $snapshot === null ? 0 : $snapshot->getVersion();
        $events = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(Event::className(), 'e')
            ->where('e.type = :type AND e.aggregateId = :aggregateId AND e.version > :version')
            ->orderBy('e.sequenceNumber')
            ->getQuery()
            ->execute(
                array(
                    'type' => $aggregateRootClass,
                    'aggregateId' => $aggregateId->getValue(),
                    'version' => $fromVersion
                )
            );
        $result = array();
        /* @var $event Event */
        foreach ($events as $event) {
            $result[] = unserialize($event->getData());
        }
        return new ArrayIterator($result);
    }

    /**
     * Events raised in the current transaction are not being stored in this snapshot.
     * Only the already persisted events are being utilized.
     *
     * @param string $aggregateRootClass
     * @param AggregateId $aggregateId
     */
    public function createSnapshot($aggregateRootClass, AggregateId $aggregateId)
    {
        $events = $this->getEventsFor($aggregateRootClass, $aggregateId);
        if ($events->count() == 0) {
            return;
        }

        /* @var $aggregateRoot EventSourcedAggregateRoot */
        $aggregateRoot = $this->loadSnapshot($aggregateRootClass, $aggregateId);
        if ($aggregateRoot === null) {
            $objectClass = new ObjectClass($aggregateRootClass);
            $aggregateRoot = $objectClass->newInstanceWithoutConstructor();
        }
        $aggregateRoot->loadFromHistory($events);

        /* @var $snapshot Snapshot */
        $snapshot = $this->findSnapshot($aggregateRootClass, $aggregateId);
        $serialized = $this->serializer->serialize($aggregateRoot);
        if ($snapshot !== null) {
            $this->entityManager->remove($snapshot);
        }
        $snapshot = new Snapshot(
            $this->getLastMetaEvent($aggregateRootClass, $aggregateId),
            $serialized,
            $aggregateRootClass
        );
        $this->entityManager->persist($snapshot);
    }

    /**
     * @param $aggregateRootClass
     * @param AggregateId $aggregateId
     * @return EventSourcedAggregateRoot|null
     */
    public function loadSnapshot($aggregateRootClass, AggregateId $aggregateId)
    {
        $aggregateRoot = null;
        /* @var $snapshot Snapshot */
        $snapshot = $this->findSnapshot($aggregateRootClass, $aggregateId);
        if ($snapshot !== null) {
            $reflectionClass = new ObjectClass($aggregateRootClass);
            $aggregateRoot = $this->serializer->deserialize($snapshot->getAggregateRoot(), $reflectionClass);
        }
        return $aggregateRoot;
    }

    /**
     * @param string $aggregateRootClass
     * @param AggregateId $aggregateId
     * @return Event
     */
    protected function getLastMetaEvent($aggregateRootClass, AggregateId $aggregateId)
    {
        return $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(Event::className(), 'e')
            ->where('e.type = :type AND e.aggregateId = :aggregateId')
            ->orderBy('e.sequenceNumber', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->setParameters(
                array(
                    'type' => $aggregateRootClass,
                    'aggregateId' => $aggregateId->getValue()
                )
            )
            ->getSingleResult();
    }

    /**
     * @param $aggregateRootClass
     * @param AggregateId $aggregateId
     * @return Aggregate|null
     */
    protected function findAggregate($aggregateRootClass, AggregateId $aggregateId)
    {
        return $this->entityManager->find(
            Aggregate::className(),
            Aggregate::createPrimaryIdArray($aggregateId, $aggregateRootClass)
        );
    }

    /**
     * @param $aggregateRootClass
     * @param AggregateId $aggregateId
     * @return Snapshot|null
     */
    protected function findSnapshot($aggregateRootClass, AggregateId $aggregateId)
    {
        return $this->entityManager->find(
            Snapshot::className(),
            Snapshot::createPrimaryIdArray($aggregateId, $aggregateRootClass)
        );
    }
}
