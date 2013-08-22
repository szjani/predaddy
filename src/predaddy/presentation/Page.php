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

use precore\lang\ObjectInterface;
use Traversable;

/**
 * Provides a page representation wich include the content and paging information as well.
 * Useful for pagination.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
interface Page extends Traversable, ObjectInterface
{
    public function getNumber();

    public function getSize();

    public function getTotalPages();

    public function getTotalElements();

    public function hasPreviousPage();

    public function isFirstPage();

    public function hasNextPage();

    public function isLastPage();

    /**
     * @return Pageable
     */
    public function nextPageable();

    /**
     * @return Pageable
     */
    public function previousPageable();

    /**
     * @return Traversable
     */
    public function getContent();

    /**
     * @return boolean
     */
    public function hasContent();

    /**
     * @return Sort
     */
    public function getSort();
}
