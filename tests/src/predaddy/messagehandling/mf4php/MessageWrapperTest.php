<?php
declare(strict_types=1);

namespace predaddy\messagehandling\mf4php;

use PHPUnit\Framework\TestCase;
use predaddy\messagehandling\SimpleMessage;

class MessageWrapperTest extends TestCase
{
    public function testSerialization()
    {
        $message = new SimpleMessage();
        $wrapper = new MessageWrapper($message);
        $serialized = serialize($wrapper);
        $unserialized = unserialize($serialized);
        self::assertInstanceOf('\predaddy\messagehandling\mf4php\MessageWrapper', $unserialized);
        self::assertEquals($message, $unserialized->getMessage());
    }
}
