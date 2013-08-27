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
use predaddy\eventhandling\DirectEventBus;
use predaddy\eventhandling\EventBus;
use predaddy\eventhandling\EventHandler;
use Traversable;

/**
 * Aggregate root class.
 *
 * You can send an event which will be handled by all handler methods
 * inside the aggregate root, after that they will be sent to all outer event handlers.
 *
 * Handler methods must be annotated with "Subscribe"
 * and must be private or protected in the aggregate root itself.
 *
 * If you are using event sourcing, you can initialize your aggregate roots through loadFromHistory method.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
abstract class AggregateRoot extends Object implements Entity, EventHandler
{
    /**
     * @var EventBus
     */
    private static $eventBus;
    private static $descriptorFactory = null;
    private $innerEventBus = null;

    private static function getDescriptorFactory()
    {
        if (self::$descriptorFactory == null) {
            self::$descriptorFactory = new AggregateRootEventHandlerDescriptorFactory();
        }
        return self::$descriptorFactory;
    }

    /**
     * @param EventBus $eventBus
     */
    public static function setEventBus(EventBus $eventBus)
    {
        self::$eventBus = $eventBus;
    }

    /**
     * Useful to replay events with loadFromHistory() from the scratch.
     * Should be used if the entity's constructor has any parameters.
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
     * @param Traversable $events
     */
    public function loadFromHistory(Traversable $events)
    {
        foreach ($events as $event) {
            $this->handleEventInAggregate($event);
        }
    }

    private function getInnerEventBus()
    {
        if ($this->innerEventBus === null) {
            $this->innerEventBus = new DirectEventBus($this->getClassName(), self::getDescriptorFactory());
            $this->innerEventBus->register($this);
        }
        return $this->innerEventBus;
    }

    private function handleEventInAggregate(DomainEvent $event)
    {
        $this->getInnerEventBus()->post($event);
    }

    protected function raise(DomainEvent $event)
    {
        if (self::$eventBus === null) {
            static::getLogger()->error("EventBus has not been set to '{}'", array(static::className()));
            throw new BadMethodCallException('EventBus has not been set!');
        }
        $this->handleEventInAggregate($event);
        self::$eventBus->post($event);
    }
}
