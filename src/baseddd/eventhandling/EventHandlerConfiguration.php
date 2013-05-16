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

namespace baseddd\eventhandling;

/**
 * Description of EventHandlerConfiguration
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class EventHandlerConfiguration
{
    private $eventHandler;
    private $handlerMethods = array();

    public function __construct(EventHandler $eventHandler)
    {
        $this->eventHandler = $eventHandler;
        $this->findHandlerMethods();
    }

    public function getHandleMethodFor(Event $event)
    {
        $eventClassName = $event->getClassName();
        if (array_key_exists($eventClassName, $this->handlerMethods)) {
            return $this->handlerMethods[$eventClassName];
        }
        $eventClass = $event->getObjectClass();
        foreach ($this->handlerMethods as $handlerEventClass => $handlerMethod) {
            if ($eventClass->isSubclassOf($handlerEventClass)) {
                return $handlerMethod;
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getHandleMethods()
    {
        return $this->handlerMethods;
    }

    /**
     * @return EventHandler
     */
    public function getEventHandler()
    {
        return $this->eventHandler;
    }

    protected function findHandlerMethods()
    {
        $eventClassName = 'baseddd\eventhandling\Event';
        foreach ($this->eventHandler->getObjectClass()->getMethods() as $reflMethod) {
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
            $this->handlerMethods[$paramType->getName()] = $reflMethod->getName();
        }
    }
}
