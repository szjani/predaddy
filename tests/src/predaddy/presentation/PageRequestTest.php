<?php
declare(strict_types=1);

namespace predaddy\presentation;

use PHPUnit\Framework\TestCase;

class PageRequestTest extends TestCase
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
        $this->sort = new Sort([$this->order]);
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

    public function testToString()
    {
        $pageRequest = new PageRequest($this->page, $this->size, $this->sort);
        self::assertEquals(
            'predaddy\presentation\PageRequest{page=3, size=4, sort=predaddy\presentation\Sort{orders=[0=predaddy\presentation\Order{prop1, DESC}]}}',
            $pageRequest->toString()
        );
    }
}
