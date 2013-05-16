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

/**
 * Description of EventHandlerConfigurationTest
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class EventHandlerConfigurationTest extends PHPUnit_Framework_TestCase
{
    private $config;

    public function setUp()
    {
        $handler = $this->getMock('baseddd\eventhandling\AllEventHandler');
        $handler
            ->expects(self::any())
            ->method('getObjectClass')
            ->will(self::returnValue(new ObjectClass($handler)));
        $this->config = new EventHandlerConfiguration($handler);
    }

    public function testGetHandleMethodFor()
    {
        $event = new SimpleEvent();
        $method = $this->config->getHandleMethodFor($event);
        self::assertNotNull($method);
    }
}
