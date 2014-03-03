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

namespace predaddy\domain;

use Iterator;
use predaddy\eventhandling\EventFunctionDescriptorFactory;
use predaddy\messagehandling\MessageBus;
use predaddy\messagehandling\MessageBusFactory;
use predaddy\messagehandling\MessageHandlerDescriptorFactory;
use predaddy\messagehandling\SimpleMessageBus;
use predaddy\messagehandling\SimpleMessageBusFactory;
use predaddy\serializer\ReflectionSerializer;
use predaddy\serializer\Serializer;
use Serializable;

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
    private static $messageBusFactory = null;

    /**
     * @var Serializer
     */
    private static $serializer = null;

    /**
     * @var MessageBus
     */
    private $innerEventBus = null;

    /**
     * @param Serializer $serializer
     */
    public static function setSerializer(Serializer $serializer = null)
    {
        self::$serializer = $serializer;
    }

    /**
     * @return Serializer
     */
    public static function getSerializer()
    {
        if (self::$serializer === null) {
            self::$serializer = new ReflectionSerializer(array('innerEventBus', 'events'));
        }
        return self::$serializer;
    }

    /**
     * @param MessageBusFactory $messageBusFactory
     */
    public static function setInnerMessageBusFactory(MessageBusFactory $messageBusFactory = null)
    {
        self::$messageBusFactory = $messageBusFactory;
    }

    /**
     * @return SimpleMessageBusFactory
     */
    public static function getInnerMessageBusFactory()
    {
        if (self::$messageBusFactory === null) {
            self::$messageBusFactory = new SimpleMessageBusFactory(
                new EventSourcingEventHandlerDescriptorFactory(
                    new EventFunctionDescriptorFactory()
                )
            );
        }
        return self::$messageBusFactory;
    }

    /**
     * Useful to replay events with loadFromHistory() from the scratch.
     *
     * @return EventSourcedAggregateRoot
     */
    final public static function createEmpty()
    {
        return static::objectClass()->newInstanceWithoutConstructor();
    }

    /**
     * Useful in case of Event Sourcing.
     *
     * @see EventSourcingRepository
     * @param Iterator $events DomainEvent iterator
     */
    public function loadFromHistory(Iterator $events)
    {
        foreach ($events as $event) {
            $this->handleEventInAggregate($event);
        }
    }

    public function serialize()
    {
        return self::getSerializer()->serialize($this);
    }

    public function unserialize($serialized)
    {
        self::getSerializer()->deserialize($serialized, static::objectClass(), $this);
        $this->events = array();
    }

    /**
     * @param DomainEvent $event
     * @deprecated Use apply() instead
     */
    protected function raise(DomainEvent $event)
    {
        $this->apply($event);
    }

    /**
     * Fire a domain event from a handler method.
     *
     * @param DomainEvent $event
     */
    protected function apply(DomainEvent $event)
    {
        $this->handleEventInAggregate($event);
        parent::raise($event);
    }

    private function getInnerEventBus()
    {
        if ($this->innerEventBus === null) {
            $this->innerEventBus = self::getInnerMessageBusFactory()->createBus($this->getClassName());
            $this->innerEventBus->register($this);
        }
        return $this->innerEventBus;
    }

    private function handleEventInAggregate(DomainEvent $event)
    {
        $this->getInnerEventBus()->post($event);
    }
}
