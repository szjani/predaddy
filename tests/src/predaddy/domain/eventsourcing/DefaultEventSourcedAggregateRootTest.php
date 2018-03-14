<?php
declare(strict_types=1);

namespace predaddy\domain\eventsourcing;

use predaddy\domain\DomainTestCase;
use predaddy\domain\UserCreated;

class DefaultEventSourcedAggregateRootTest extends DomainTestCase
{
    public function testLoadFromHistory()
    {
        $user = new EventSourcedUser(new CreateEventSourcedUser());
        self::assertEquals(EventSourcedUser::DEFAULT_VALUE, $user->value);

        // increment 3 times
        $user->increment(new Increment());
        $user->increment(new Increment());
        $user->increment(new Increment());

        $raisedEvents = $this->getAndClearRaisedEvents();

        // 1 create and 3 increment events raised
        self::assertInstanceOf(UserCreated::className(), $raisedEvents->current());
        self::assertEquals(4, count($raisedEvents));

        /* @var $replayedUser EventSourcedUser */
        $replayedUser = EventSourcedUser::objectClass()->newInstanceWithoutConstructor();
        $replayedUser->loadFromHistory($raisedEvents);

        // the two user have the same values
        self::assertNotNull($user->getId());
        self::assertEquals($user->getId(), $replayedUser->getId());
        self::assertEquals($user->value, $replayedUser->value);

        // increment only $replayedUser's value
        $replayedUser->increment(new Increment());
        $raisedEvents = $this->getAndClearRaisedEvents();
        self::assertEquals($user->value + 1, $replayedUser->value);
        self::assertEquals(1, count($raisedEvents));

        // replay the last event coming from $replayedUser on the original user
        $user->loadFromHistory($raisedEvents);
        self::assertEquals($user->value, $replayedUser->value);
    }

    public function testSerialization()
    {
        $user = new EventSourcedUser(new CreateEventSourcedUser());
        $user->increment(new Increment());
        $serialized = serialize($user);
        /* @var $resUser EventSourcedUser */
        $resUser = unserialize($serialized);
        self::assertEquals($user->getId(), $resUser->getId());
        self::assertEquals($user->value, $resUser->value);
    }
}
