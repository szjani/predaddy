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

namespace predaddy\domain;

use PHPUnit_Framework_TestCase;
use precore\util\UUID;
use predaddy\fixture\article\EventSourcedArticleId;
use predaddy\fixture\article\EventSourcedArticleId2;

/**
 * @package src\predaddy\domain
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class UUIDAggregateIdTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreatedFromValue()
    {
        $value = UUID::randomUUID()->toString();
        $articleId = EventSourcedArticleId::from($value);
        self::assertInstanceOf(EventSourcedArticleId::className(), $articleId);
        self::assertEquals($value, $articleId->value());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldFailIfBuiltFromInvalidString()
    {
        EventSourcedArticleId::from('invalid');
    }

    /**
     * @test
     */
    public function shouldCheckReference()
    {
        /* @var $id UUIDAggregateId */
        $id = $this->getMockForAbstractClass(UUIDAggregateId::className(), [], '', false);
        $id
            ->expects(self::never())
            ->method('value');
        $id
            ->expects(self::never())
            ->method('aggregateClass');

        self::assertTrue($id->equals($id));
    }

    /**
     * @test
     */
    public function shouldNotBeEqualTwoDifferentTypeOfAggregateId()
    {
        $value = UUID::randomUUID()->toString();
        $id1 = EventSourcedArticleId::from($value);
        $id2 = EventSourcedArticleId2::from($value);
        self::assertFalse($id1->equals($id2));
        self::assertFalse($id2->equals($id1));
    }
}
