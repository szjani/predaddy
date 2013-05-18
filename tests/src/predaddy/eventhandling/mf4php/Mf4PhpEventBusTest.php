<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace predaddy\eventhandling\mf4php;

use mf4php\DefaultQueue;
use mf4php\memory\MemoryMessageDispatcher;
use PHPUnit_Framework_TestCase;
use precore\lang\ObjectClass;
use predaddy\eventhandling\DeadEvent;
use predaddy\eventhandling\SimpleEvent;

require_once __DIR__ . '/../SimpleEvent.php';
require_once __DIR__ . '/../SimpleEventHandler.php';
require_once __DIR__ . '/../AllEventHandler.php';
require_once __DIR__ . '/../DeadEventHandler.php';
require_once __DIR__ . '/SimpleEventObjectMessageFactory.php';

/**
 * Description of DirectEventBusTest
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class Mf4PhpEventBusTest extends PHPUnit_Framework_TestCase
{
    private $bus;

    public function setUp()
    {
        $dispatcher = new MemoryMessageDispatcher();
        $this->bus = new Mf4PhpEventBus('default', $dispatcher);
        $dispatcher->addListener(new DefaultQueue('default'), $this->bus);
    }

    public function testTwoHandlerPost()
    {
        $event = new SimpleEvent();
        $simpleEventHandler = $this->getMock('predaddy\eventhandling\SimpleEventHandler');
        $allEventHandler = $this->getMock('predaddy\eventhandling\AllEventHandler');

        $simpleEventHandler
            ->expects(self::any())
            ->method('getObjectClass')
            ->will(self::returnValue(new ObjectClass($simpleEventHandler)));
        $simpleEventHandler
            ->expects(self::any())
            ->method('getClassName')
            ->will(self::returnValue('predaddy\eventhandling\SimpleEventHandler'));
        $simpleEventHandler
            ->expects(self::once())
            ->method('handle')
            ->with($event);

        $allEventHandler
            ->expects(self::any())
            ->method('getObjectClass')
            ->will(self::returnValue(new ObjectClass($allEventHandler)));
        $allEventHandler
            ->expects(self::any())
            ->method('getClassName')
            ->will(self::returnValue('predaddy\eventhandling\AllEventHandler'));
        $allEventHandler
            ->expects(self::once())
            ->method('handle')
            ->with($event);

        $this->bus->register($simpleEventHandler);
        $this->bus->register($allEventHandler);
        $this->bus->post($event);
        $this->bus->unregister($simpleEventHandler);
        $this->bus->unregister($allEventHandler);
        $this->bus->post($event);
    }

    public function testDeadEventHandling()
    {
        $expectedEvent = $event = new SimpleEvent();
        $deadEventHandler = $this->getMock('predaddy\eventhandling\DeadEventHandler');

        $deadEventHandler
            ->expects(self::any())
            ->method('getObjectClass')
            ->will(self::returnValue(new ObjectClass($deadEventHandler)));
        $deadEventHandler
            ->expects(self::any())
            ->method('getClassName')
            ->will(self::returnValue('predaddy\eventhandling\DeadEventHandler'));
        $deadEventHandler
            ->expects(self::once())
            ->method('handle')
            ->will(
                self::returnCallback(
                    function ($event) use ($expectedEvent) {
                        PHPUnit_Framework_TestCase::assertInstanceOf(DeadEvent::className(), $event);
                        PHPUnit_Framework_TestCase::assertSame($expectedEvent, $event->getEvent());
                    }
                )
            );

        $this->bus->register($deadEventHandler);
        $this->bus->post($event);
    }

    public function testMessageFactory()
    {
        $factory = $this->getMock(__NAMESPACE__ . '\SimpleEventObjectMessageFactory', array('createMessage'));
        $factory
            ->expects(self::once())
            ->method('createMessage')
            ->will(
                self::returnCallback(
                    function (EventWrapper $wrapper) {
                        return new \mf4php\ObjectMessage($wrapper);
                    }
                )
            );

        $event = new SimpleEvent();
        $simpleEventHandler = $this->getMock('predaddy\eventhandling\SimpleEventHandler');
        $simpleEventHandler
            ->expects(self::any())
            ->method('getObjectClass')
            ->will(self::returnValue(new ObjectClass($simpleEventHandler)));
        $simpleEventHandler
            ->expects(self::any())
            ->method('getClassName')
            ->will(self::returnValue('predaddy\eventhandling\SimpleEventHandler'));
        $simpleEventHandler
            ->expects(self::once())
            ->method('handle')
            ->with($event);

        $this->bus->register($simpleEventHandler);
        $this->bus->registerObjectMessageFactory($factory);
        $this->bus->post($event);
    }
}
