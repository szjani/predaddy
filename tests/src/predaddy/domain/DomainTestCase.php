<?php
declare(strict_types=1);

namespace predaddy\domain;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use predaddy\EventCollector;
use predaddy\eventhandling\EventBus;
use predaddy\eventhandling\EventFunctionDescriptorFactory;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;

/**
 * @package predaddy\domain
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class DomainTestCase extends TestCase
{
    /**
     * @var EventBus
     */
    protected $eventBus;

    /**
     * @var EventCollector
     */
    private $eventCollector;

    protected function setUp()
    {
        $this->eventBus = new EventBus();
        $this->eventCollector = new EventCollector();
        $this->eventBus->register($this->eventCollector);
        EventPublisher::instance()->setEventBus($this->eventBus);
    }

    protected function getAndClearRaisedEvents()
    {
        return new ArrayIterator($this->eventCollector->events());
    }
}
