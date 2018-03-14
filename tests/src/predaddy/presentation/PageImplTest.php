<?php
declare(strict_types=1);

namespace predaddy\presentation;

use PHPUnit\Framework\TestCase;

class PageImplTest extends TestCase
{
    /**
     * @var PageImpl
     */
    private $page;

    private $content;

    private $sort;

    private $pageNumber = 2;

    private $size = 4;

    private $total = 101;

    public function setUp()
    {
        $this->sort = new Sort([new Order(Direction::$DESC, 'prop1')]);
        $pageable = new PageRequest($this->pageNumber, $this->size, $this->sort);
        $record1 = 'record1';
        $record2 = 'record2';
        $this->content = [$record1, $record2];
        $this->page = new PageImpl([$record1, $record2], $pageable, $this->total);
    }

    public function testGetters()
    {
        self::assertSame($this->sort, $this->page->getSort());
        self::assertEquals(new \ArrayObject($this->content), $this->page->getContent());
        self::assertEquals($this->pageNumber, $this->page->getNumber());
        self::assertEquals($this->size, $this->page->getSize());
        self::assertEquals($this->total, $this->page->getTotalElements());
        self::assertEquals(26, $this->page->getTotalPages());
    }

    public function testHasContent()
    {
        self::assertTrue($this->page->hasContent());
        $emptyPage = new PageImpl([]);
        self::assertFalse($emptyPage->hasContent());
    }

    public function testHasNextAndPrevPageAndIssers()
    {
        self::assertTrue($this->page->hasNextPage());

        $size = 2;

        $page0 = new PageImpl($this->content, new PageRequest(0, $size), 5);
        self::assertTrue($page0->hasNextPage());
        self::assertFalse($page0->hasPreviousPage());
        self::assertTrue($page0->isFirstPage());

        $page1 = new PageImpl($this->content, new PageRequest(1, $size), 5);
        self::assertTrue($page1->hasNextPage());
        self::assertTrue($page1->hasPreviousPage());

        $page2 = new PageImpl($this->content, new PageRequest(2, $size), 5);
        self::assertFalse($page2->hasNextPage());
        self::assertTrue($page2->hasPreviousPage());
        self::assertTrue($page2->isLastPage());
    }

    public function testPrevAndNextPageable()
    {
        $prev = $this->page->previousPageable();
        self::assertEquals($this->pageNumber - 1, $prev->getPageNumber());

        $next = $this->page->nextPageable();
        self::assertEquals($this->pageNumber + 1, $next->getPageNumber());

        $firstAndLastPage = new PageImpl([], new PageRequest(0, 3));
        self::assertNull($firstAndLastPage->previousPageable());
        self::assertNull($firstAndLastPage->nextPageable());
    }

    public function testGetIterator()
    {
        $it = $this->page->getIterator();
        self::assertEquals(count($this->content), $it->count());
        self::assertSame($this->content, $it->getArrayCopy());
    }

    public function testEquals()
    {
        self::assertFalse($this->page->equals(null));
        self::assertTrue($this->page->equals($this->page));
        $pageable = new PageRequest($this->pageNumber, $this->size, $this->sort);
        $page = new PageImpl($this->content, $pageable, $this->total);
        self::assertTrue($this->page->equals($page));
    }

    public function testToString()
    {
        self::assertEquals(
            'predaddy\presentation\PageImpl{total=101, pageable=predaddy\presentation\PageRequest{page=2, size=4, sort=predaddy\presentation\Sort{orders=[0=predaddy\presentation\Order{prop1, DESC}]}}}',
            $this->page->toString()
        );
    }
}
