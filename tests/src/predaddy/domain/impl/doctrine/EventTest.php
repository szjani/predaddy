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

use DateTime;
use PHPUnit_Framework_TestCase;
use precore\util\UUID;
use predaddy\domain\UUIDAggregateId;

class EventTest extends PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $aggregateId = new UUIDAggregateId(UUID::randomUUID());
        $type = __CLASS__;
        $version = 2;
        $created = new DateTime();
        $serializedEvent = __METHOD__;
        $stateHash = UUID::randomUUID()->toString();
        $domainEvent = $this->getMock('\predaddy\domain\DomainEvent');
        $domainEvent
            ->expects(self::once())
            ->method('getClassName')
            ->will(self::returnValue('\predaddy\domain\DomainEvent'));
        $domainEvent
            ->expects(self::once())
            ->method('getTimestamp')
            ->will(self::returnValue($created));
        $domainEvent
            ->expects(self::once())
            ->method('getStateHash')
            ->will(self::returnValue($stateHash));

        $event = new Event($aggregateId->getValue(), $type, $domainEvent, $version, $serializedEvent);
        self::assertEquals($version, $event->getVersion());
        self::assertEquals($type, $event->getAggregateType());
        self::assertEquals($aggregateId->getValue(), $event->getAggregateId());
        self::assertEquals($created, $event->getCreated());
        self::assertEquals($serializedEvent, $event->getData());
        self::assertEquals($stateHash, $event->getStateHash());
    }
}
