<?php
/*
 * Copyright (c) 2013 Janos Szurovecz
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
use predaddy\commandhandling\CommandFunctionDescriptor;
use ReflectionClass;
use ReflectionFunction;

class DefaultFunctionDescriptorTest extends PHPUnit_Framework_TestCase
{
    private static $anyWrapper;
    private static $anyPriority;

    public static function setUpBeforeClass()
    {
        self::$anyWrapper = new ClosureWrapper(function ($message) {});
        self::$anyPriority = 1;
    }

    public function testIsHandlerFor()
    {
        $function = function (Message $message) {};
        $descriptor = new DefaultFunctionDescriptor(new ClosureWrapper($function), self::$anyPriority);
        self::assertTrue($descriptor->isHandlerFor(SimpleMessage::objectClass()));
        self::assertTrue($descriptor->isHandlerFor(DeadMessage::objectClass()));
    }

    public function testInvalidHandlerFunction()
    {
        $function = function ($message) {};
        $descriptor = new DefaultFunctionDescriptor(new ClosureWrapper($function), self::$anyPriority);
        self::assertFalse($descriptor->isHandlerFor(SimpleMessage::objectClass()));
    }

    public function testComparison()
    {
        $function1 = function ($message) {};
        $descriptor1 = new DefaultFunctionDescriptor(new ClosureWrapper($function1), 1);
        $function2 = function ($message) {};
        $descriptor2 = new DefaultFunctionDescriptor(new ClosureWrapper($function2), 2);
        self::assertGreaterThan(0, $descriptor1->compareTo($descriptor2));
    }

    /**
     * @test
     */
    public function shouldReturnTheGivenPriority()
    {
        $priority = 2;
        $descriptor = new DefaultFunctionDescriptor(self::$anyWrapper, $priority);
        self::assertEquals($priority, $descriptor->getPriority());
    }

    /**
     * @test
     */
    public function shouldNotBeEqualTwoDifferentTypeOfDescriptors()
    {
        $descriptor1 = new DefaultFunctionDescriptor(self::$anyWrapper, self::$anyPriority);
        $descriptor2 = new CommandFunctionDescriptor(self::$anyWrapper, self::$anyPriority);
        self::assertFalse($descriptor1->equals($descriptor2));
        self::assertFalse($descriptor2->equals($descriptor1));
    }

    /**
     * @test
     * @expectedException \precore\lang\ClassCastException
     */
    public function shouldNotBeComparableWithOtherType()
    {
        $descriptor = new DefaultFunctionDescriptor(self::$anyWrapper, self::$anyPriority);
        $descriptor->compareTo($this);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function shouldNotBeComparableWithNull()
    {
        $descriptor = new DefaultFunctionDescriptor(self::$anyWrapper, self::$anyPriority);
        $descriptor->compareTo(null);
    }
}
