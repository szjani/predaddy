<?php
declare(strict_types=1);

namespace predaddy\messagehandling;


use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class MethodWrapperTest extends TestCase
{
    public function testToString()
    {
        $wrapper = new MethodWrapper($this, new ReflectionMethod($this, 'testToString'));
        self::assertTrue(false !== strpos($wrapper->toString(), get_class($this)));
    }
}
