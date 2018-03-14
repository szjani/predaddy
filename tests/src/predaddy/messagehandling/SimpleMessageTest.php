<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use PHPUnit\Framework\TestCase;

class SimpleMessageTest extends TestCase
{
    public function testToString()
    {
        $message = new SimpleMessage();
        self::assertTrue(false !== strpos($message->toString(), $message->identifier()));
    }

    public function testSerialization()
    {
        $message = new SimpleMessage();
        $serialized = serialize($message);
        $unserialized = unserialize($serialized);
        self::assertEquals($message, $unserialized);
    }
}
