<?php
/*
 * Copyright (c) 2013 Szurovecz János
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

namespace predaddy\presentation;

use PHPUnit_Framework_TestCase;

class PageRequestTest extends PHPUnit_Framework_TestCase
{
    private $page = 3;

    private $size = 4;

    private $order;

    private $sort;

    /**
     * @var PageRequest
     */
    private $pageRequest;

    public function setUp()
    {
        $this->order = new Order(Direction::$DESC, 'prop1');
        $this->sort = new Sort(array($this->order));
        $this->pageRequest = new PageRequest($this->page, $this->size, $this->sort);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidPage()
    {
        new PageRequest(-1, 3);
    }

    public function testGetters()
    {
        self::assertEquals(12, $this->pageRequest->getOffset());
        self::assertEquals($this->page, $this->pageRequest->getPageNumber());
        self::assertEquals($this->size, $this->pageRequest->getPageSize());
        self::assertTrue($this->pageRequest->getSort()->equals($this->sort));
    }

    public function testFirst()
    {
        $firstPageable = $this->pageRequest->first();
        self::assertEquals(0, $firstPageable->getOffset());
        self::assertEquals(0, $firstPageable->getPageNumber());
    }

    public function testHasPrevious()
    {
        self::assertTrue($this->pageRequest->hasPrevious());
        self::assertFalse($this->pageRequest->first()->hasPrevious());
    }

    public function testNext()
    {
        $next = $this->pageRequest->next();
        self::assertEquals($this->page + 1, $next->getPageNumber());
        self::assertEquals($this->size, $next->getPageSize());
        self::assertSame($this->sort, $next->getSort());
    }

    public function testPreviousOrFirst()
    {
        $first = $this->pageRequest->first();
        self::assertSame($first, $first->previousOrFirst());
        $prev = $this->pageRequest->previousOrFirst();
        self::assertEquals($this->page - 1, $prev->getPageNumber());
    }

    public function testEquals()
    {
        $pageRequest = new PageRequest($this->page, $this->size, $this->sort);
        self::assertTrue($this->pageRequest->equals($pageRequest));
        self::assertTrue($pageRequest->equals($pageRequest));
        self::assertFalse($pageRequest->equals(null));
        $pageRequest2 = new PageRequest($this->page + 1, $this->size + 1, $this->sort);
        self::assertFalse($pageRequest->equals($pageRequest2));
    }
} 