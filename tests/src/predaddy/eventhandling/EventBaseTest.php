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

use DateTime;
use PHPUnit_Framework_TestCase;

require_once 'SimpleEvent.php';

/**
 * Description of EventBaseTest
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class EventBaseTest extends PHPUnit_Framework_TestCase
{
    public function testEquals()
    {
        $event1 = new SimpleEvent();
        $event2 = new SimpleEvent();
        self::assertFalse($event1->equals($event2));
        self::assertTrue($event1->equals($event1));
    }

    public function testGetTimestamp()
    {
        $event = new SimpleEvent();
        self::assertInstanceOf('DateTime', $event->getTimestamp());
        self::assertTrue($event->getTimestamp() <= new DateTime());
    }

    public function testSerialize()
    {
        $event = new SimpleEvent();
        $text = serialize($event);
        $deserialized = unserialize($text);
        self::assertEquals($event->getPrivateData(), $deserialized->getPrivateData());
        self::assertEquals($event->getProtectedData(), $deserialized->getProtectedData());
        self::assertEquals($event->getEventIdentifier(), $deserialized->getEventIdentifier());
    }

    public function testDeserializeFromOldFormatWherePrivateDataIsMissing()
    {
        $oldFormat = 'C:34:"predaddy\eventhandling\SimpleEvent":253:{a:4:{s:4:"data";s:5:"hello";s:13:"protectedData";s:9:"protected";s:2:"id";s:36:"65b76f03-b066-4259-b16c-a9f621e5e880";s:9:"timestamp";O:8:"DateTime":3:{s:4:"date";s:19:"2013-08-25 15:29:37";s:13:"timezone_type";i:3;s:8:"timezone";s:13:"Europe/Berlin";}}}';
        /* @var $event SimpleEvent */
        $event = unserialize($oldFormat);
        self::assertNull($event->getPrivateData());
        self::assertNotEquals('', $event->getProtectedData());
    }
}
