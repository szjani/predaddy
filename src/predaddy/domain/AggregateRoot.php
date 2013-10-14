<?php
/*
 * Copyright (c) 2013 Szurovecz János
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

use BadMethodCallException;
use precore\lang\Object;
use precore\lang\ObjectInterface;
use predaddy\messagehandling\event\AnnotationBasedEventBus;
use predaddy\messagehandling\event\EventFunctionDescriptorFactory;
use predaddy\messagehandling\MessageBus;
use predaddy\messagehandling\MessageHandlerDescriptorFactory;
use predaddy\messagehandling\SimpleMessageBus;
use Traversable;

/**
 * Aggregate root class.
 *
 * You can send an event which will be handled by all handler methods
 * inside the aggregate root, after that they will be sent to all outer event handlers.
 *
 * Handler methods must be annotated with "Subscribe"
 * and must be private or protected methods. You can override this behaviour
 * with setInnerDescriptorFactory() method.
 *
 * If you are using event sourcing, you can initialize your aggregate roots through loadFromHistory() method.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
abstract class AggregateRoot extends Object implements Entity
{
    /**
     * @var MessageBus
     */
    private static $eventBus;
    private static $descriptorFactory = null;
    private $innerEventBus = null;

    /**
     * @return AggregateRootEventHandlerDescriptorFactory
     */
    public static function getInnerDescriptorFactory()
    {
        if (self::$descriptorFactory === null) {
            self::$descriptorFactory = new AggregateRootEventHandlerDescriptorFactory(
                new EventFunctionDescriptorFactory()
            );
        }
        return self::$descriptorFactory;
    }

    /**
     * It's recommended to use AggregateRootEventHandlerDescriptorFactory.
     *
     * @param MessageHandlerDescriptorFactory $descriptorFactory
     */
    public static function setInnerDescriptorFactory(MessageHandlerDescriptorFactory $descriptorFactory)
    {
        self::$descriptorFactory = $descriptorFactory;
    }

    /**
     * All domain events raised in the aggregate roots
     * are posted to $messageBus.
     *
     * @param MessageBus $eventBus
     */
    public static function setEventBus(MessageBus $eventBus)
    {
        self::$eventBus = $eventBus;
    }

    public static function getEventBus()
    {
        return self::$eventBus;
    }

    /**
     * Useful to replay events with loadFromHistory() from the scratch.
     *
     * @return AggregateRoot
     */
    final public static function createEmpty()
    {
        return unserialize(sprintf('O:%u:"%s":0:{}', strlen(static::className()), static::className()));
    }

    /**
     * Useful in case of Event Sourcing.
     *
     * @param DomainEvent[] $events
     */
    public function loadFromHistory($events)
    {
        foreach ($events as $event) {
            $this->handleEventInAggregate($event);
        }
    }

    protected function raise(DomainEvent $event)
    {
        if (self::getEventBus() === null) {
            static::getLogger()->error("Message bus has not been set to '{}'", array(static::className()));
            throw new BadMethodCallException('Message bus has not been set to AggregateRoot!');
        }
        $this->handleEventInAggregate($event);
        self::getEventBus()->post($event);
    }

    private function getInnerEventBus()
    {
        if ($this->innerEventBus === null) {
            $this->innerEventBus = new SimpleMessageBus(
                $this->getClassName(),
                self::getInnerDescriptorFactory(),
                self::getInnerDescriptorFactory()->getFunctionDescriptorFactory()
            );
            $this->innerEventBus->register($this);
        }
        return $this->innerEventBus;
    }

    private function handleEventInAggregate(DomainEvent $event)
    {
        $this->getInnerEventBus()->post($event);
    }
}
