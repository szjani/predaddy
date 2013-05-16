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

namespace baseddd\domain;

use BadMethodCallException;
use precore\lang\Object;

/**
 * Description of AggregateRoot
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
abstract class AggregateRoot extends Object implements Entity
{
    /**
     * @var EventBus
     */
    private static $eventBus;

    /**
     * @param EventBus $eventBus
     */
    public static function setEventBus(EventBus $eventBus)
    {
        self::$eventBus = $eventBus;
    }

    protected static function raise(DomainEvent $event)
    {
        if (self::$eventBus === null) {
            static::getLogger()->error("EventBus has not been set to '{}'", array(static::className()));
            throw new BadMethodCallException('EventBus has not been set!');
        }
        self::$eventBus->post($event);
    }
}
