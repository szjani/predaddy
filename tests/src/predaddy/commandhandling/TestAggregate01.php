<?php
/*
 * Copyright (c) 2012-2014 Janos Szurovecz
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
    public function getId()
    {
        return $this->id;
    }
}
