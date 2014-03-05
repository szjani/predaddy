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

use DateTime;
use PHPUnit_Framework_TestCase;

require_once 'SimpleMessage.php';

/**
 * Description of EventBaseTest
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class MessageBaseTest extends PHPUnit_Framework_TestCase
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
        self::assertInstanceOf('DateTime', $message->getTimestamp());
        self::assertTrue($message->getTimestamp() <= new DateTime());
    }

    public function testSerialize()
    {
        $message = new SimpleMessage();
        $text = serialize($message);
        $deserialized = unserialize($text);
        self::assertEquals($message->getPrivateData(), $deserialized->getPrivateData());
        self::assertEquals($message->getProtectedData(), $deserialized->getProtectedData());
        self::assertEquals($message->getMessageIdentifier(), $deserialized->getMessageIdentifier());
        self::assertEquals(SimpleMessage::PRIVATE_VALUE, $deserialized->getPrivateData());
    }
}
