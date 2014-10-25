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

use precore\lang\Object;

/**
 * It probably does not need to be used in application level.
 *
 * @see predaddy\domain\AbstractDomainEvent
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class NullAggregateId extends Object implements AggregateId
{
    private static $instance;

    /**
     * @return NullAggregateId
     */
    public static function instance()
    {
        return self::$instance;
    }

    /**
     * Should not be called!
     */
    public static function init()
    {
        self::$instance = new NullAggregateId();
    }

    private function __construct()
    {
    }

    /**
     * @return string
     */
    public function value()
    {
        return '';
    }

    /**
     * @return string
     */
    public function aggregateClass()
    {
        return '';
    }
}
NullAggregateId::init();
