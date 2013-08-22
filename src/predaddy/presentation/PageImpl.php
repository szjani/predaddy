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

use ArrayIterator;
use IteratorAggregate;
use precore\lang\Object;
use precore\lang\ObjectInterface;

/**
 * Default Page implementation.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class PageImpl extends Object implements IteratorAggregate, Page
{
    private $content = array();

    /**
     * @var Pageable
     */
    private $pageable;

    /**
     * @var int
     */
    private $total;

    /**
     * @param array $content
     * @param Pageable $pageable
     * @param int $total Total number of all elements (not just the current page's)
     */
    public function __construct(array $content, Pageable $pageable = null, $total = null)
    {
        $this->content = array_merge($this->content, $content);
        $this->total = $total === null ? count($content) : $total;
        $this->pageable = $pageable;
    }

    /**
     * @return array of the records in the current page
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return int The page number
     */
    public function getNumber()
    {
        return $this->pageable === null
            ? 0
            : $this->pageable->getPageNumber();
    }

    /**
     * @return int size of the content
     */
    public function getSize()
    {
        return $this->pageable === null
            ? 0
            : $this->pageable->getPageSize();
    }

    public function getSort()
    {
        return $this->pageable === null
            ? null
            : $this->pageable->getSort();
    }

    public function getTotalElements()
    {
        return $this->total;
    }

    public function getTotalPages()
    {
        return $this->getSize() == 0
            ? 1
            : ceil($this->total / $this->getSize());
    }

    public function hasContent()
    {
        return !empty($this->content);
    }

    public function hasNextPage()
    {
        return $this->getNumber() + 1 < $this->getTotalPages();
    }

    public function hasPreviousPage()
    {
        return 0 < $this->getNumber();
    }

    public function isFirstPage()
    {
        return !$this->hasPreviousPage();
    }

    public function isLastPage()
    {
        return !$this->hasNextPage();
    }

    /**
     * Create a Pageable object to be able to obtain the next page.
     *
     * @return Pageable
     */
    public function nextPageable()
    {
        return $this->hasNextPage()
            ? $this->pageable->next()
            : null;
    }

    /**
     * Create a Pageable object to be able to obtain the previous page.
     *
     * @return Pageable
     */
    public function previousPageable()
    {
        return $this->hasPreviousPage()
            ? $this->pageable->previousOrFirst()
            : null;
    }

    /**
     * @return ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->content);
    }

    public function equals(ObjectInterface $object = null)
    {
        if ($object === $this) {
            return true;
        }
        if (!($object instanceof self)) {
            return false;
        }
        $totalEqual = $this->total === $object->total;
        $contentEqual = $this->content === $object->content;
        $pageableEqual = $this->pageable === null
            ? $object->pageable === null
            : $this->pageable->equals($object->pageable);

        return $totalEqual && $contentEqual && $pageableEqual;
    }
}
