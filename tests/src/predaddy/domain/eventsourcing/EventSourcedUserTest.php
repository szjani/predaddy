<?php

namespace predaddy\domain\eventsourcing;

use PHPUnit_Framework_TestCase;
use predaddy\domain\DecrementedEvent;
use predaddy\domain\IncrementedEvent;
use predaddy\domain\UserCreated;
use predaddy\util\test\Fixtures;

/**
 * Class EventSourcedUserTest
 *
 * @package predaddy\domain\eventsourcing
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class EventSourcedUserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreated()
    {
        $fixture = Fixtures::newGivenWhenThenFixture(EventSourcedUser::className());
        $fixture
            ->when(new CreateEventSourcedUser())
            ->expectEvents(new UserCreated());
    }

    /**
     * @test
     */
    public function shouldIncremented()
    {
        $fixture = Fixtures::newGivenWhenThenFixture(EventSourcedUser::className());
        $fixture
            ->givenEvents(new UserCreated())
            ->when($fixture->fill(new Increment()))
            ->expectReturnValue(2)
            ->expectEvents(new IncrementedEvent());
    }

    /**
     * @test
     */
    public function shouldDecremented()
    {
        $fixture = Fixtures::newGivenWhenThenFixture(EventSourcedUser::className());
        $fixture
            ->givenEvents(
                new UserCreated(),
                new IncrementedEvent()
            )
            ->when($fixture->fill(new Decrement()))
            ->expectEvents(new DecrementedEvent(1));
    }
}
