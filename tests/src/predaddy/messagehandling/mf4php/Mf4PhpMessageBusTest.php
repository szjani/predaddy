<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace predaddy\messagehandling\mf4php;

use mf4php\memory\MemoryMessageDispatcher;
use mf4php\ObjectMessage;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\DefaultFunctionDescriptorFactory;
use predaddy\messagehandling\SimpleMessage;
use predaddy\messagehandling\SimpleMessageBusTest;
use predaddy\messagehandling\SimpleMessageHandler;

require_once __DIR__ . '/../SimpleMessageBusTest.php';

/**
 * Description of Mf4PhpMessageBusTest
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class Mf4PhpMessageBusTest extends SimpleMessageBusTest
{
    public function setUp()
    {
        $dispatcher = new MemoryMessageDispatcher();
        $functionDescFactory = new DefaultFunctionDescriptorFactory();
        $this->bus = new Mf4PhpMessageBus(
            'default',
            new AnnotatedMessageHandlerDescriptorFactory($functionDescFactory),
            $functionDescFactory,
            $dispatcher
        );
    }

    public function testMessageFactory()
    {
        $factory = $this->getMock(__NAMESPACE__ . '\ObjectMessageFactory', array('createMessage'));
        $factory
            ->expects(self::once())
            ->method('createMessage')
            ->will(
                self::returnCallback(
                    function (SimpleMessage $message) {
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

    public function testSuccessCallback()
    {
        self::markTestSkipped("Mf4phpMessageBus does not support MessageCallback!");
    }

    public function testFailedCallback()
    {
        self::markTestSkipped("Mf4phpMessageBus does not support MessageCallback!");
    }
}
