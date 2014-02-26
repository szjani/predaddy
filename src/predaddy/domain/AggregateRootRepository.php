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
use precore\lang\Object;
use predaddy\eventhandling\EventBus;

/**
 * Can be used in DDD/CQRS architecture.
 *
 * @package predaddy\domain
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
abstract class AggregateRootRepository extends Object implements Repository
{
    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @param EventBus $eventBus
     */
    public function __construct(EventBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * @param AggregateRoot $aggregateRoot
     * @param Iterator $events
     * @param int $version
     * @return void
     */
    abstract protected function innerSave(AggregateRoot $aggregateRoot, Iterator $events, $version);

    public function save(AggregateRoot $aggregateRoot, $version)
    {
        $events = $aggregateRoot->getAndClearRaisedEvents();
        $this->innerSave($aggregateRoot, $events, $version);
        foreach ($events as $event) {
            $this->getEventBus()->post($event);
        }
    }

    /**
     * @return EventBus
     */
    public function getEventBus()
    {
        return $this->eventBus;
    }
}
