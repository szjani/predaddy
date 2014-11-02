<?php

namespace predaddy\domain;

use PHPUnit_Framework_TestCase;
use predaddy\inmemory\InMemoryRepository;
use predaddy\util\test\Fixtures;

/**
 * Class UserTest
 *
 * @package predaddy\domain
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class UserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldIncrementUser()
    {
        $fixture = Fixtures::newGivenWhenThenFixture(User::className());
        $fixture
            ->registerAnnotatedCommandHandler(new UserCommandHandler(new InMemoryRepository()))
            ->givenCommands(new CreateUser())
            ->when(new Increment())
            ->expectReturnValue(2)
            ->expectEvents(new IncrementedEvent());
    }

    /**
     * @test
     */
    public function shouldIncrementTwoTimes()
    {
        $repository = new InMemoryRepository();
        $fixture = Fixtures::newGivenWhenThenFixture(User::className());
        $fixture
            ->registerAnnotatedCommandHandler(new UserCommandHandler($repository))
            ->givenCommands(
                new CreateUser(),
                new Increment()
            )
            ->when(new Increment())
            ->expectReturnValue(3)
            ->expectEvents(new IncrementedEvent());

        /* @var $aggregateRoot User */
        $aggregateRoot = $repository->load($fixture->getAggregateId());
        self::assertInstanceOf(User::className(), $aggregateRoot);
        self::assertEquals(3, $aggregateRoot->value);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionIfIncrementedThreeTimes()
    {
        $fixture = Fixtures::newGivenWhenThenFixture(User::className());
        $fixture
            ->registerAnnotatedCommandHandler(new UserCommandHandler(new InMemoryRepository()))
            ->givenCommands(
                new CreateUser(),
                new Increment(),
                new Increment()
            )
            ->when(new Increment())
            ->expectException('\RuntimeException', 'Cannot be incremented more, than twice');
    }
}
