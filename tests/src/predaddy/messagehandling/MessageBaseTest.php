<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Description of EventBaseTest
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class MessageBaseTest extends TestCase
{
    public function testEquals()
    {
        $message2 = new SimpleMessage();
        $event2 = new SimpleMessage();
        self::assertFalse($message2->equals($event2));
        self::assertTrue($message2->equals($message2));
    }

    public function testGetTimestamp()
    {
        $message = new SimpleMessage();
        self::assertInstanceOf('DateTime', $message->created());
        self::assertTrue($message->created() <= new DateTime());
    }

    public function testSerialize()
    {
        $message = new SimpleMessage();
        $text = serialize($message);
        $deserialized = unserialize($text);
        self::assertEquals($message->getPrivateData(), $deserialized->getPrivateData());
        self::assertEquals($message->getProtectedData(), $deserialized->getProtectedData());
        self::assertEquals($message->identifier(), $deserialized->identifier());
        self::assertEquals(SimpleMessage::PRIVATE_VALUE, $deserialized->getPrivateData());
    }

    /**
     * @test
     * @expectedException \precore\lang\NullPointerException
     */
    public function shouldThrowNpeIfCreatedIsNull()
    {
        $message = new NullCreatedMessage();
        $message->created();
    }

    /**
     * @test
     * @expectedException \precore\lang\NullPointerException
     */
    public function shouldThrowNpeIfIdIsNull()
    {
        $message = new NullCreatedMessage();
        $message->identifier();
    }
}

final class NullCreatedMessage extends AbstractMessage
{
    public function __construct()
    {
    }
}
