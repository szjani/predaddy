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

use precore\lang\ObjectClass;
use precore\util\UUID;
use predaddy\domain\DefaultAggregateId;
use predaddy\domain\DomainEvent;
use predaddy\domain\DomainTestCase;
use predaddy\domain\EventPublisher;
use predaddy\domain\IncrementedEvent;
use predaddy\domain\User;
use predaddy\domain\UserCreated;
use predaddy\domain\UUIDAggregateId;

class DoctrineAggregateRootRepositoryTest extends DomainTestCase
{
    const AR_CLASS = 'predaddy\domain\User';

    private $entityManager;

    /**
     * @var DoctrineAggregateRootRepository
     */
    private $repository;

    protected function setUp()
    {
        parent::setUp();
        $this->entityManager = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $this->repository = $this->getMock(
            DoctrineAggregateRootRepository::className(),
            ['currentVersion'],
            [$this->entityManager]
        );

        $this->repository
            ->expects(self::any())
            ->method('currentVersion')
            ->will(self::returnValue(1));
    }

    /**
     * @test
     */
    public function persistNewAggregate()
    {
        $version = null;
        $aggregateRoot = new User();

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($aggregateRoot);

        $this->repository->save($aggregateRoot, $version);
    }

    /**
     * @test
     */
    public function updateAggregate()
    {
        $aggregateRoot = new User();

        $this->entityManager
            ->expects(self::once())
            ->method('contains')
            ->with($aggregateRoot)
            ->will(self::returnValue(true));

        $this->entityManager
            ->expects(self::never())
            ->method('persist');

        $this->repository->save($aggregateRoot);
    }

    public function testGetters()
    {
        self::assertSame($this->entityManager, $this->repository->getEntityManager());
    }

    public function testLoad()
    {
        $aggregateRootId = new DefaultAggregateId(UUID::randomUUID()->toString(), self::AR_CLASS);
        $user = new User();
        $this->entityManager
            ->expects(self::once())
            ->method('find')
            ->with(self::AR_CLASS, $aggregateRootId->value())
            ->will(self::returnValue($user));

        $user = $this->repository->load($aggregateRootId);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function exceptionIfIdIsInvalid()
    {
        $this->entityManager
            ->expects(self::once())
            ->method('find')
            ->will(self::returnValue(null));

        $this->repository->load(new DefaultAggregateId(UUID::randomUUID()->toString(), __CLASS__));
    }

    /**
     * @test
     */
    public function versionMustBeSet()
    {
        EventPublisher::instance()->setEventBus($this->eventBus);
        $user = new User();
        $user->increment();
        $events = $this->getAndClearRaisedEvents();
        $this->repository->save($user);
        /* @var $createdEvent UserCreated */
        $createdEvent = $events[0];
        /* @var $incrementedEvent IncrementedEvent */
        $incrementedEvent = $events[1];
        self::assertInstanceOf(UserCreated::className(), $createdEvent);
        self::assertInstanceOf(IncrementedEvent::className(), $incrementedEvent);

        self::assertNotNull($createdEvent->stateHash());
        self::assertNotNull($incrementedEvent->stateHash());
    }
}
