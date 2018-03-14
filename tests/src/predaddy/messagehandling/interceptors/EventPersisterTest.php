<?php
declare(strict_types=1);

namespace predaddy\messagehandling\interceptors;

use PHPUnit\Framework\TestCase;
use predaddy\messagehandling\InterceptorChain;

/**
 * @package src\predaddy\messagehandling\interceptors
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class EventPersisterTest extends TestCase
{
    /**
     * @var EventPersister
     */
    private $persister;
    private $eventStore;

    /**
     * @var InterceptorChain
     */
    private static $anyChain;

    protected function setUp()
    {
        $this->eventStore = $this->getMockBuilder('\predaddy\domain\EventStore')->getMock();
        $this->persister = new EventPersister($this->eventStore);
        self::$anyChain = $this->getMockBuilder('\predaddy\messagehandling\InterceptorChain')->getMock();
    }

    /**
     * @test
     * @expectedException \precore\lang\ClassCastException
     */
    public function nonEventShouldCauseException()
    {
        $invalidMessage = $this;
        $this->persister->invoke($invalidMessage, self::$anyChain);
    }

    /**
     * @test
     */
    public function eventShouldBePersisted()
    {
        $event = $this->getMockBuilder('\predaddy\domain\DomainEvent')->getMock();
        $chain = $this->getMockBuilder('\predaddy\messagehandling\InterceptorChain')->getMock();
        $chain
            ->expects(self::once())
            ->method('proceed');
        $this->eventStore
            ->expects(self::once())
            ->method('persist')
            ->with($event);
        $this->persister->invoke($event, $chain);
    }
}
