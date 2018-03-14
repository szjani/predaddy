<?php
declare(strict_types=1);

namespace predaddy\domain\eventsourcing;

use Iterator;
use predaddy\domain\AbstractAggregateRoot;
use predaddy\domain\DomainEvent;
use predaddy\eventhandling\EventBus;
use predaddy\eventhandling\EventFunctionDescriptorFactory;
use predaddy\messagehandling\MessageBus;
use predaddy\messagehandling\annotation\Subscribe;

/**
 * You can send an event which will be handled by all handler methods
 * inside the aggregate root, after that they will be sent to all outer event handlers.
 *
 * Handler methods must be annotated with "Subscribe"
 * and must be private or protected methods. You can override this behaviour
 * with setInnerMessageBusFactory() method.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class AbstractEventSourcedAggregateRoot extends AbstractAggregateRoot implements EventSourcedAggregateRoot
{
    /**
     * @var EventSourcingEventHandlerDescriptorFactory
     */
    private static $descriptorFactory;

    public static function init() : void
    {
        self::$descriptorFactory = new EventSourcingEventHandlerDescriptorFactory(
            new EventFunctionDescriptorFactory()
        );
    }

    /**
     * @param AbstractEventSourcedAggregateRoot $aggregateRoot
     * @return EventBus
     */
    private static function createInnerEventBus(AbstractEventSourcedAggregateRoot $aggregateRoot) : EventBus
    {
        $bus = EventBus::builder()
            ->withIdentifier(static::className())
            ->withHandlerDescriptorFactory(self::$descriptorFactory)
            ->build();
        $bus->register($aggregateRoot);
        return $bus;
    }

    /**
     * Useful in case of Event Sourcing.
     *
     * @see EventSourcingRepository
     * @param Iterator $events DomainEvent iterator
     */
    final public function loadFromHistory(Iterator $events) : void
    {
        $bus = self::createInnerEventBus($this);
        foreach ($events as $event) {
            $this->handleEventInAggregate($event, $bus);
        }
    }

    /**
     * Fire a domain event from a handler method.
     *
     * @param DomainEvent $event
     */
    final protected function apply(DomainEvent $event) : void
    {
        $this->handleEventInAggregate($event);
        parent::raise($event);
    }

    /**
     * Updates stateHash field when replaying events. Should not be called directly.
     *
     * @Subscribe
     * @param DomainEvent $event
     */
    final protected function updateStateHash(DomainEvent $event) : void
    {
        $this->setStateHash($event->stateHash());
    }

    private function handleEventInAggregate(DomainEvent $event, MessageBus $innerBus = null) : void
    {
        if ($innerBus === null) {
            $innerBus = self::createInnerEventBus($this);
        }
        $innerBus->post($event);
    }
}
AbstractEventSourcedAggregateRoot::init();
