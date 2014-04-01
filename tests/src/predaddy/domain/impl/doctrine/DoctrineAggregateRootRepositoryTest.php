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

use Doctrine\DBAL\LockMode;
use PHPUnit_Framework_TestCase;
use precore\lang\ObjectClass;
use precore\util\UUID;
use predaddy\domain\User;
use predaddy\domain\UUIDAggregateId;

require_once __DIR__ . '/../../User.php';

class DoctrineAggregateRootRepositoryTest extends PHPUnit_Framework_TestCase
{
    const AR_CLASS = 'predaddy\domain\User';

    private $entityManager;

    private $lockMode = LockMode::PESSIMISTIC_READ;

    /**
     * @var DoctrineAggregateRootRepository
     */
    private $repository;

    private $eventBus;

    protected function setUp()
    {
        $this->entityManager = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $this->eventBus = $this->getMock('\predaddy\messagehandling\MessageBus');
        $this->repository = new DoctrineAggregateRootRepository(
            $this->eventBus,
            ObjectClass::forName(self::AR_CLASS),
            $this->entityManager,
            $this->lockMode
        );
    }

    public function testLockMode()
    {
        self::assertEquals($this->lockMode, $this->repository->getLockMode());
    }

    /**
     * @test
     */
    public function persistNewAggregate()
    {
        $version = 0;
        $aggregateRoot = new User();

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($aggregateRoot);

        $this->entityManager
            ->expects(self::never())
            ->method('lock');

        $this->repository->save($aggregateRoot, $version);
    }

    /**
     * @test
     */
    public function updateAggregate()
    {
        $version = 1;
        $aggregateRoot = new User();

        $this->entityManager
            ->expects(self::never())
            ->method('persist');

        $this->entityManager
            ->expects(self::once())
            ->method('lock')
            ->with($aggregateRoot, $this->lockMode, $version);

        $this->repository->save($aggregateRoot, $version);
    }

    public function testGetters()
    {
        self::assertSame($this->entityManager, $this->repository->getEntityManager());
        self::assertSame(self::AR_CLASS, $this->repository->getAggregateRootClass()->getName());
    }

    public function testLoad()
    {
        $aggregateRootId = new UUIDAggregateId(UUID::randomUUID());
        $user = new User();
        $this->entityManager
            ->expects(self::once())
            ->method('find')
            ->with(self::AR_CLASS, $aggregateRootId->getValue())
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

        $this->repository->load(new UUIDAggregateId(UUID::randomUUID()));
    }
}
