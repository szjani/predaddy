<?php
declare(strict_types=1);

namespace predaddy\serializer;

use Doctrine\Common\Annotations\AnnotationRegistry;
use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\TestCase;
use predaddy\fixture\article\EventSourcedArticleId;
use predaddy\fixture\BaseEvent;
use predaddy\fixture\SimpleCommand;

class JmsSerializerTest extends TestCase
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
        AnnotationRegistry::registerAutoloadNamespace(
            'JMS\Serializer\Annotation',
            VENDOR . "/jms/serializer/src"
        );
    }

    public function testSerialize()
    {
        $jmsSerializer = $this->getMockBuilder('\JMS\Serializer\SerializerInterface')->getMock();
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
            ->with($serialized, TestClass::className(), $format)
            ->will(self::returnValue($obj));

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
        $stateHash = '001';
        $event = new BaseEvent(EventSourcedArticleId::create());
        $event->setStateHash($stateHash);
        $ser = $this->serializer->serialize($event);
        $res = $this->serializer->deserialize($ser, BaseEvent::objectClass());
        self::assertTrue($event->equals($res));
        self::assertEquals($stateHash, $res->stateHash());
    }

    public function testCommandSerialization()
    {
        $aggregateId = uniqid();
        $arg1 = 'arg1';
        $arg2 = 'arg2';
        $command = new SimpleCommand($aggregateId, $arg1, $arg2);
        $ser = $this->serializer->serialize($command);
        $res = $this->serializer->deserialize($ser, SimpleCommand::objectClass());
        self::assertEquals($command, $res);
    }
}
