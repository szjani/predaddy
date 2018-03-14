<?php
declare(strict_types=1);

namespace predaddy\domain;

use precore\lang\BaseObject;
use predaddy\messagehandling\MessageBus;
use predaddy\messagehandling\util\NullMessageBus;

/**
 * Intended to get all DomainEvents and forward them to the given bus.
 * AggregateRoots should send events directly to it in order to preserve events' order.
 *
 * Should be initialized with a properly constructed bus in your application setup.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class EventPublisher extends BaseObject
{
    /**
     * @var EventPublisher
     */
    private static $instance;

    /**
     * @var NullMessageBus
     */
    private static $nullMessageBus;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * Should not be called!
     */
    public static function init() : void
    {
        self::$nullMessageBus = new NullMessageBus();
        self::$instance = new EventPublisher();
    }

    private function __construct()
    {
        $this->eventBus = self::$nullMessageBus;
    }

    /**
     * @param MessageBus $eventBus
     */
    public function setEventBus(MessageBus $eventBus = null) : void
    {
        $this->eventBus = $eventBus ?: self::$nullMessageBus;
        self::getLogger()->debug('Event bus has been set to EventPublisher: [{}]', [$this->eventBus]);
    }

    /**
     * @return EventPublisher
     */
    public static function instance() : EventPublisher
    {
        return self::$instance;
    }

    /**
     * @param DomainEvent $event
     */
    public function post(DomainEvent $event) : void
    {
        self::getLogger()->debug('DomainEvent raised [{}], forwarding to the event bus...', [$event]);
        $this->eventBus->post($event);
    }
}
EventPublisher::init();
