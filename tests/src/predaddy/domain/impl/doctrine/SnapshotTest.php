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
use predaddy\domain\UUIDAggregateId;

class SnapshotTest extends PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $aggregateRoot = __METHOD__;
        $type = __CLASS__;
        $aggregateId = new DefaultAggregateId(UUID::randomUUID()->toString(), $type);
        $version = 2;
        $snapshot = new Snapshot($aggregateId->value(), $aggregateRoot, $type, $version);
        self::assertEquals($aggregateId->value(), $snapshot->getAggregateId());
        self::assertEquals($version, $snapshot->getVersion());
        self::assertEquals($type, $snapshot->getType());
        self::assertEquals($aggregateRoot, $snapshot->getAggregateRoot());
    }

    public function testUpdate()
    {
        $aggregateRoot = __METHOD__;
        $type = __CLASS__;
        $aggregateId = new DefaultAggregateId(UUID::randomUUID()->toString(), $type);
        $version = 2;
        $snapshot = new Snapshot($aggregateId->value(), $aggregateRoot, $type, $version);
        $newData = 'data';
        $version = 3;
        $snapshot->update($newData, $version);
        self::assertEquals($newData, $snapshot->getAggregateRoot());
        self::assertEquals($version, $snapshot->getVersion());
    }
}
