<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace predaddy\messagehandling\mf4php;

use mf4php\DefaultQueue;
use mf4php\memory\MemoryMessageDispatcher;
use mf4php\ObjectMessage;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\DefaultFunctionDescriptorFactory;
use predaddy\messagehandling\SimpleMessage;
use predaddy\messagehandling\SimpleMessageBusTest;
use predaddy\messagehandling\SimpleMessageHandler;

/**
 * Description of Mf4PhpMessageBusTest
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class Mf4PhpMessageBusTest extends SimpleMessageBusTest
{
    /**
     * @var MemoryMessageDispatcher
     */
    private $dispatcher;

    public function setUp()
    {
        $this->dispatcher = new MemoryMessageDispatcher();
        $this->bus = Mf4PhpMessageBus::builder($this->dispatcher)->build();
    }

    public function testMessageFactory()
    {
        $factory = $this->getMock(__NAMESPACE__ . '\ObjectMessageFactory', ['createMessage']);
        $factory
            ->expects(self::once())
            ->method('createMessage')
            ->will(
                self::returnCallback(
                    function (MessageWrapper $message) {
                        return new ObjectMessage($message);
                    }
                )
            );

        $event = new SimpleMessage();
        $simpleEventHandler = new SimpleMessageHandler();

        $this->bus->register($simpleEventHandler);
        $this->bus->registerObjectMessageFactory($factory, SimpleMessage::className());
        $this->bus->post($event);

        self::assertSame($event, $simpleEventHandler->lastMessage);
    }

    public function successCallback()
    {
        self::markTestSkipped("Mf4phpMessageBus does not support MessageCallback!");
    }

    public function failedCallback()
    {
        self::markTestSkipped("Mf4phpMessageBus does not support MessageCallback!");
    }

    public function exceptionInDeadMessageHandlerIsBeingPassedToCallback()
    {
        self::markTestSkipped("Mf4phpMessageBus does not support MessageCallback!");
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function postInvalidMessageToDispatcher()
    {
        $message = $this->getMock('\mf4php\Message');
        $this->dispatcher->send(new DefaultQueue(Mf4PhpMessageBusBuilder::DEFAULT_NAME), $message);
    }
}
