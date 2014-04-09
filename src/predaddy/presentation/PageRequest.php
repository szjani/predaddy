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

use InvalidArgumentException;
use precore\lang\Object;
use precore\lang\ObjectInterface;
use precore\util\Objects;

/**
 * Default implementation of Pageable.
 * The first page number is 0.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class PageRequest extends Object implements Pageable
{
    private $page;
    private $size;
    private $sort;

    /**
     * @param int $page Started from 0
     * @param int $size Size of the page
     * @param Sort $sort
     * @throws InvalidArgumentException if $page is < 0
     */
    public function __construct($page, $size, Sort $sort = null)
    {
        if ($page < 0) {
            throw new InvalidArgumentException('Page must be at least 0');
        }
        $this->page = (int) $page;
        $this->size = (int) $size;
        $this->sort = $sort;
    }

    /**
     * @return Pageable
     */
    public function first()
    {
        return new PageRequest(0, $this->size, $this->sort);
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->page * $this->size;
    }

    /**
     * @return int
     */
    public function getPageNumber()
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getPageSize()
    {
        return $this->size;
    }

    /**
     * @return Sort
     */
    public function getSort()
    {
        return $this->sort;
    }

    public function hasPrevious()
    {
        return 0 < $this->page;
    }

    /**
     * @return Pageable
     */
    public function next()
    {
        return new PageRequest($this->page + 1, $this->size, $this->sort);
    }

    /**
     * @return Pageable
     */
    public function previousOrFirst()
    {
        return $this->hasPrevious()
            ? new PageRequest($this->page - 1, $this->size, $this->sort)
            : $this;
    }

    public function equals(ObjectInterface $object = null)
    {
        if ($object === $this) {
            return true;
        }
        if (!($object instanceof self)) {
            return false;
        }

        return Objects::equal($this->page, $object->page)
            && Objects::equal($this->size, $object->size)
            && Objects::equal($this->sort, $object->sort);
    }

    public function toString()
    {
        return Objects::toStringHelper($this)
            ->add('page', $this->page)
            ->add('size', $this->size)
            ->add('sort', $this->sort)
            ->toString();
    }
}
