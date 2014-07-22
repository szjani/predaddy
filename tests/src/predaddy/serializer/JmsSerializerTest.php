<?php
/*
 * Copyright (c) 2012-2014 Szurovecz János
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

namespace predaddy\serializer;

use JMS\Serializer\SerializerBuilder;
use PHPUnit_Framework_TestCase;
use predaddy\fixture\article\ArticleId;
use predaddy\fixture\article\EventSourcedArticleId;
use predaddy\fixture\BaseEvent;

class JmsSerializerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var JmsSerializer
     */
    private $serializer;

    protected function setUp()
    {
        $builder = SerializerBuilder::create();
        $builder->addMetadataDir(__DIR__ . '/../../../../src/resources/jms');
        $this->serializer = new JmsSerializer($builder->build(), 'xml');
    }

    public function testSerialize()
    {
        $jmsSerializer = $this->getMock('\JMS\Serializer\SerializerInterface');
        $format = 'json';
        $serializer = new JmsSerializer($jmsSerializer, $format);

        $obj = new TestClass();
        $serialized = 'serialized';

        $jmsSerializer
            ->expects(self::once())
            ->method('serialize')
            ->with($obj, $format)
            ->will(self::returnValue($serialized));

        $jmsSerializer
            ->expects(self::once())
            ->method('deserialize')
            ->with($serialized, TestClass::className(), $format);

        self::assertEquals($serialized, $serializer->serialize($obj));
        $serializer->deserialize($serialized, TestClass::objectClass(), $format);
    }

    public function testIntegration()
    {
        $articleId = EventSourcedArticleId::create();
        $serialized = $this->serializer->serialize($articleId);
        $res = $this->serializer->deserialize($serialized, EventSourcedArticleId::objectClass());
        self::assertTrue($articleId->equals($res));
    }

    public function testEventSerialization()
    {
        $event = new BaseEvent(EventSourcedArticleId::create());
        $ser = $this->serializer->serialize($event);
        $res = $this->serializer->deserialize($ser, BaseEvent::objectClass());
        self::assertTrue($event->equals($res));
    }
}
