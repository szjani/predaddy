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

use PHPUnit_Framework_TestCase;
use precore\util\UUID;
use predaddy\domain\DefaultAggregateId;
use predaddy\domain\impl\doctrine\entities\UserCreated;
use predaddy\domain\UUIDAggregateId;

class AggregateTest extends PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $type = __CLASS__;
        $aggregateId = new DefaultAggregateId(UUID::randomUUID()->toString(), $type);
        $aggregate = new Aggregate($aggregateId, $type);
        self::assertEquals($aggregateId->value(), $aggregate->getAggregateId());
        self::assertEquals($type, $aggregate->getType());
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    public function otherAggregateRelatedEventCannotBeAdded()
    {
        $type = __CLASS__;
        $aggregateId = new DefaultAggregateId(UUID::randomUUID()->toString(), $type);
        $aggregate = new Aggregate($aggregateId);
        $otherAggregateId = new DefaultAggregateId(UUID::randomUUID()->toString(), $type);
        $aggregate->createMetaEvent(new UserCreated($otherAggregateId), null);
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    public function otherAggregateTypeRelatedEventCannotBeAdded()
    {
        $aggregateId = new DefaultAggregateId(UUID::randomUUID()->toString(), __CLASS__);
        $aggregate = new Aggregate($aggregateId);
        $otherAggregateId = new DefaultAggregateId($aggregateId->value(), DefaultAggregateId::className());
        $aggregate->createMetaEvent(new UserCreated($otherAggregateId), null);
    }
}
