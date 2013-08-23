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

namespace predaddy\eventhandling;

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

require_once 'SimpleEvent.php';

class DefaultFunctionDescriptorTest extends PHPUnit_Framework_TestCase
{
    public function testIsHandlerFor()
    {
        $function = function (Event $event) {};
        $descriptor = new DefaultFunctionDescriptor(new ReflectionFunction($function));
        self::assertTrue($descriptor->isHandlerFor(SimpleEvent::objectClass()));
        self::assertTrue($descriptor->isHandlerFor(new ReflectionClass(__NAMESPACE__ . '\Event')));
        self::assertTrue($descriptor->isHandlerFor(DeadEvent::objectClass()));
        self::assertFalse($descriptor->isHandlerFor(new ReflectionClass('\stdClass')));
    }
} 