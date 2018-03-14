<?php
declare(strict_types=1);

namespace predaddy\inmemory;

use PHPUnit\Framework\TestCase;
use predaddy\domain\AggregateId;
use predaddy\domain\GenericAggregateId;

/**
 * @package predaddy\domain\impl
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class InMemoryRepositoryTest extends TestCase
{
    private static $AN_AGGREGATE_ID;

    /**
     * @var InMemoryRepository
     */
    private $repository;

    public function setUp()
    {
        $this->repository = new InMemoryRepository();
        self::$AN_AGGREGATE_ID = new GenericAggregateId('1', __CLASS__);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfNoPersistedAggregate()
    {
        $this->repository->load(self::$AN_AGGREGATE_ID);
    }

    /**
     * @test
     */
    public function shouldReturnPersistedAggregate()
    {
        $aggregateId = self::$AN_AGGREGATE_ID;
        $aggregate = $this->anyAggregateWithId($aggregateId);
        $this->repository->save($aggregate);
        self::assertSame($aggregate, $this->repository->load($aggregateId));
    }

    private function anyAggregateWithId(AggregateId $aggregateId)
    {
        $aggregate = $this->getMockForAbstractClass('\predaddy\domain\AbstractAggregateRoot');
        $aggregate
            ->expects(self::any())
            ->method('getId')
            ->will(self::returnValue($aggregateId));
        return $aggregate;
    }
}
