<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace predaddy\eventhandling\mf4php;

use mf4php\memory\MemoryMessageDispatcher;
use mf4php\ObjectMessage;
use predaddy\eventhandling\DirectEventBusTest;
use predaddy\eventhandling\SimpleEvent;
use predaddy\eventhandling\SimpleEventHandler;

require_once __DIR__ . '/../DirectEventBusTest.php';

/**
 * Description of DirectEventBusTest
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class Mf4PhpEventBusTest extends DirectEventBusTest
{
    public function setUp()
    {
        $dispatcher = new MemoryMessageDispatcher();
        $this->bus = new Mf4PhpEventBus('default', $dispatcher);
    }

    public function testMessageFactory()
    {
        $factory = $this->getMock(__NAMESPACE__ . '\ObjectMessageFactory', array('createMessage'));
        $factory
            ->expects(self::once())
            ->method('createMessage')
            ->will(
                self::returnCallback(
                    function (SimpleEvent $event) {
                        return new ObjectMessage($event);
                    }
                )
            );

        $event = new SimpleEvent();
        $simpleEventHandler = new SimpleEventHandler();

        $this->bus->register($simpleEventHandler);
        $this->bus->registerObjectMessageFactory($factory, SimpleEvent::className());
        $this->bus->post($event);

        self::assertSame($event, $simpleEventHandler->lastEvent);
    }
}
