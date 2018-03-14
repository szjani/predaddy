<?php
declare(strict_types=1);

namespace predaddy\domain;

/**
 * Description of AggregateRootTest
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class AggregateRootTest extends DomainTestCase
{
    public function testCallHandleMethod()
    {
        $user = new User();
        self::assertEquals(User::DEFAULT_VALUE, $user->value);

        $user->increment();
        $events = $this->getAndClearRaisedEvents();
        self::assertEquals(2, $user->value);
        self::assertTrue($events->valid());
        self::assertEquals($events->current()->aggregateId(), $user->getId());
    }

    public function testEquals()
    {
        $user = new User();
        $clone = $this->getMockBuilder(User::className())
            ->setConstructorArgs([''])
            ->setMethods(['getId'])
            ->getMock();
        $clone
            ->expects(self::once())
            ->method('getId')
            ->will(self::returnValue($user->getId()));
        self::assertTrue($user->equals($clone));
    }

    public function testToString()
    {
        $user = new User();
        self::assertStringStartsWith(User::className(), $user->toString());
    }

    /**
     * @test
     */
    public function stateHashMatching()
    {
        $user = new User();
        $events = $this->getAndClearRaisedEvents();
        self::assertCount(1, $events);
        $event = $events[0];
        /* @var $event DomainEvent */
        self::assertEquals($event->stateHash(), $user->stateHash());
        $user->failWhenStateHashViolation($event->stateHash());
    }

    /**
     * @test
     * @expectedException \precore\lang\IllegalStateException
     */
    public function stateHashViolationShouldThrowException()
    {
        $user = new User();
        $events = $this->getAndClearRaisedEvents();
        self::assertCount(1, $events);
        $event = $events[0];
        /* @var $event DomainEvent */
        self::assertEquals($event->stateHash(), $user->stateHash());

        $user->increment();
        $events = $this->getAndClearRaisedEvents();
        self::assertCount(1, $events);
        $event = $events[0];
        $user->failWhenStateHashViolation($event->stateHash());

        $user->failWhenStateHashViolation('invalid');
    }
}
