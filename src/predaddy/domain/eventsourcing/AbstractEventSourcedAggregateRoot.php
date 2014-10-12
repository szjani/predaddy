<?php
/*
 * Copyright (c) 2012-2014 Szurovecz János
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
 * @author Szurovecz János <szjani@szjani.hu>
 */
abstract class AbstractEventSourcedAggregateRoot extends AbstractAggregateRoot implements EventSourcedAggregateRoot
{
    /**
     * @var EventSourcingEventHandlerDescriptorFactory
     */
    private static $descriptorFactory;

    public static function init()
    {
        self::$descriptorFactory = new EventSourcingEventHandlerDescriptorFactory(
            new EventFunctionDescriptorFactory()
        );
    }

    /**
     * @param AbstractEventSourcedAggregateRoot $aggregateRoot
     * @return EventBus
     */
    private static function createInnerEventBus(AbstractEventSourcedAggregateRoot $aggregateRoot)
    {
        $bus = new EventBus(static::className(), [], null, self::$descriptorFactory);
        $bus->register($aggregateRoot);
        return $bus;
    }

    /**
     * Useful in case of Event Sourcing.
     *
     * @see EventSourcingRepository
     * @param Iterator $events DomainEvent iterator
     */
    final public function loadFromHistory(Iterator $events)
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
    final protected function apply(DomainEvent $event)
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
    final protected function updateStateHash(DomainEvent $event)
    {
        $this->setStateHash($event->stateHash());
    }

    private function handleEventInAggregate(DomainEvent $event, MessageBus $innerBus = null)
    {
        if ($innerBus === null) {
            $innerBus = self::createInnerEventBus($this);
        }
        $innerBus->post($event);
    }
}
AbstractEventSourcedAggregateRoot::init();
