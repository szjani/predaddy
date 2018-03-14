<?php
declare(strict_types=1);

namespace predaddy\commandhandling;

use precore\util\UUID;
use predaddy\domain\AbstractAggregateRoot;
use predaddy\domain\AggregateId;
use predaddy\domain\UUIDAggregateId;
use RuntimeException;
use predaddy\messagehandling\annotation\Subscribe;

/**
 * @package predaddy\commandhandling
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class TestAggregate01 extends AbstractAggregateRoot
{
    const RESULT = 'Hello World';

    private $id;

    public function __construct()
    {
        $this->id = TestAggregate01Id::create();
    }

    /**
     * @Subscribe
     * @param ThrowException $command
     * @throws \RuntimeException
     */
    public function throwException(ThrowException $command)
    {
        throw new RuntimeException('Expected exception');
    }

    /**
     * @Subscribe
     * @param ReturnResult $command
     * @return string
     */
    public function returnResult(ReturnResult $command)
    {
        return self::RESULT;
    }

    /**
     * @return AggregateId
     */
    public function getId() : AggregateId
    {
        return $this->id;
    }
}
