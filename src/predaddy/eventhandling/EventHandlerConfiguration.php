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

namespace predaddy\eventhandling;

use Doctrine\Common\Annotations\Reader;

/**
 * Description of EventHandlerConfiguration
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class EventHandlerConfiguration
{
    private $eventHandler;
    private $directHandlerMethods = array();
    private $reader;
    private $compatibleHandlerMethodsCache = array();

    public function __construct(EventHandler $eventHandler, Reader $reader)
    {
        $this->eventHandler = $eventHandler;
        $this->reader = $reader;
        $this->findHandlerMethods();
    }

    /**
     * @param Event $event
     * @return array
     */
    public function getHandlerMethodsFor(Event $event)
    {
        $eventClassName = $event->getClassName();
        if (!array_key_exists($eventClassName, $this->compatibleHandlerMethodsCache)) {
            $this->compatibleHandlerMethodsCache[$eventClassName] = $this->findCompatibleMethodsFor($event);
        }
        return $this->compatibleHandlerMethodsCache[$eventClassName];
    }

    /**
     * @return EventHandler
     */
    public function getEventHandler()
    {
        return $this->eventHandler;
    }

    /**
     * Find all handler methods for a specific type of Event
     *
     * @param Event $event
     * @return array
     */
    protected function findCompatibleMethodsFor(Event $event)
    {
        $result = array();
        $eventClass = $event->getObjectClass();
        foreach ($this->directHandlerMethods as $handlerEventClass => $methods) {
            if ($eventClass->getName() === $handlerEventClass || $eventClass->isSubclassOf($handlerEventClass)) {
                $result = array_merge($result, $methods);
            }
        }
        return $result;
    }

    protected function findHandlerMethods()
    {
        $eventClassName = 'predaddy\eventhandling\Event';
        $reflClass = $this->eventHandler->getObjectClass();
        foreach ($reflClass->getMethods() as $reflMethod) {
            if ($this->reader->getMethodAnnotation($reflMethod, __NAMESPACE__ . '\Subscribe') === null) {
                continue;
            }
            if (!$reflMethod->isPublic()) {
                continue;
            }
            $params = $reflMethod->getParameters();
            if (count($params) !== 1) {
                continue;
            }
            /* @var $paramType ReflectionClass */
            $paramType = $params[0]->getClass();
            if ($paramType === null
                || (!$paramType->isSubclassOf($eventClassName) && $paramType->getName() !== $eventClassName)) {
                continue;
            }
            $this->directHandlerMethods[$paramType->getName()][] = $reflMethod->getName();
        }
    }
}
