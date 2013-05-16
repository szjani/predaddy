<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace baseddd\eventhandling;

use PHPUnit_Framework_TestCase;
use precore\lang\ObjectClass;

require_once 'SimpleEvent.php';
require_once 'SimpleEventHandler.php';
require_once 'AllEventHandler.php';
require_once 'DeadEventHandler.php';

/**
 * Description of DirectEventBusTest
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class DirectEventBusTest extends PHPUnit_Framework_TestCase
{
    private $bus;

    public function setUp()
    {
        $this->bus = new DirectEventBus('default');
    }

    public function testTwoHandlerPost()
    {
        $event = new SimpleEvent();
        $simpleEventHandler = $this->getMock('baseddd\eventhandling\SimpleEventHandler');
        $allEventHandler = $this->getMock('baseddd\eventhandling\AllEventHandler');

        $simpleEventHandler
            ->expects(self::any())
            ->method('getObjectClass')
            ->will(self::returnValue(new ObjectClass($simpleEventHandler)));
        $simpleEventHandler
            ->expects(self::any())
            ->method('getClassName')
            ->will(self::returnValue('baseddd\eventhandling\SimpleEventHandler'));
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
            ->will(self::returnValue('baseddd\eventhandling\AllEventHandler'));
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
        $deadEventHandler = $this->getMock('baseddd\eventhandling\DeadEventHandler');

        $deadEventHandler
            ->expects(self::any())
            ->method('getObjectClass')
            ->will(self::returnValue(new ObjectClass($deadEventHandler)));
        $deadEventHandler
            ->expects(self::any())
            ->method('getClassName')
            ->will(self::returnValue('baseddd\eventhandling\DeadEventHandler'));
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
}
