<?php
/*
 * Copyright (c) 2014 Janos Szurovecz
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
