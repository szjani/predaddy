<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use PHPUnit\Framework\TestCase;
use predaddy\commandhandling\CommandFunctionDescriptor;
use ReflectionClass;
use ReflectionFunction;

class DefaultFunctionDescriptorTest extends TestCase
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
        self::assertTrue($descriptor->isHandlerFor(new SimpleMessage()));
        self::assertTrue($descriptor->isHandlerFor(new DeadMessage(null)));
    }

    public function testInvalidHandlerFunction()
    {
        $function = function ($message) {};
        $descriptor = new DefaultFunctionDescriptor(new ClosureWrapper($function), self::$anyPriority);
        self::assertFalse($descriptor->isHandlerFor(new SimpleMessage()));
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
