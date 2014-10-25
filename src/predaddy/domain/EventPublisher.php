<?php
/*
 * Copyright (c) 2012-2014 Janos Szurovecz
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

use precore\lang\Object;
use predaddy\eventhandling\EventBus;
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
final class EventPublisher extends Object
{
    private static $instance;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * Should not be called!
     */
    public static function init()
    {
        self::$instance = new EventPublisher();
    }

    private function __construct()
    {
        $this->eventBus = new NullMessageBus();
    }

    /**
     * @param EventBus $eventBus
     */
    public function setEventBus(EventBus $eventBus = null)
    {
        $this->eventBus = $eventBus ?: new NullMessageBus();
        self::getLogger()->debug('Event bus has been set to EventPublisher: [{}]', [$this->eventBus]);
    }

    /**
     * @return EventPublisher
     */
    public static function instance()
    {
        return self::$instance;
    }

    /**
     * @param DomainEvent $event
     */
    public function post(DomainEvent $event)
    {
        self::getLogger()->debug('DomainEvent raised [{}], forwarding to the event bus...', [$event]);
        $this->eventBus->post($event);
    }
}
EventPublisher::init();
