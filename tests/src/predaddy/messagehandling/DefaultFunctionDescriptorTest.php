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

namespace predaddy\messagehandling;

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

require_once 'SimpleMessage.php';

class DefaultFunctionDescriptorTest extends PHPUnit_Framework_TestCase
{
    public function testIsHandlerFor()
    {
        $function = function (Message $message) {};
        $descriptor = new DefaultFunctionDescriptor(new ReflectionFunction($function), 1);
        self::assertTrue($descriptor->isHandlerFor(SimpleMessage::objectClass()));
        self::assertTrue($descriptor->isHandlerFor(DeadMessage::objectClass()));
    }

    public function testInvalidHandlerFunction()
    {
        $function = function ($message) {};
        $descriptor = new DefaultFunctionDescriptor(new ReflectionFunction($function), 1);
        self::assertFalse($descriptor->isHandlerFor(SimpleMessage::objectClass()));
    }
}
