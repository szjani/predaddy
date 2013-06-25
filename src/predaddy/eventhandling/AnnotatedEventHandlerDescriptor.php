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
use ReflectionClass;
use ReflectionMethod;

/**
 * Finds handler methods which are annotated with Subscribe.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class AnnotatedEventHandlerDescriptor implements EventHandlerDescriptor
{
    private $handlerClass;
    private $directHandlerMethods = array();
    private $compatibleHandlerMethodsCache = array();
    private $reader;

    public function __construct(ReflectionClass $handlerClass, Reader $reader)
    {
        $this->handlerClass = $handlerClass;
        $this->reader = $reader;
        $this->findHandlerMethods();
    }

    /**
     * @param ReflectionClass $eventClass
     * @return array of ReflectionMethod
     */
    public function getHandlerMethodsFor(ReflectionClass $eventClass)
    {
        $eventClassName = $eventClass->getName();
        if (!array_key_exists($eventClassName, $this->compatibleHandlerMethodsCache)) {
            $this->compatibleHandlerMethodsCache[$eventClassName] = $this->findCompatibleMethodsFor($eventClass);
        }
        return $this->compatibleHandlerMethodsCache[$eventClassName];
    }

    /**
     * Find all handler methods for a specific type of Event
     *
     * @param ReflectionClass $eventClass
     * @return array of ReflectionMethod
     */
    protected function findCompatibleMethodsFor(ReflectionClass $eventClass)
    {
        $result = array();
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
        /* @var $reflMethod ReflectionMethod */
        foreach ($this->handlerClass->getMethods() as $reflMethod) {
            if ($this->reader->getMethodAnnotation($reflMethod, __NAMESPACE__ . '\Subscribe') === null) {
                continue;
            }
            if (!$this->isVisible($reflMethod)) {
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
            $reflMethod->setAccessible(true);
            $this->directHandlerMethods[$paramType->getName()][] = $reflMethod;
        }
    }

    protected function isVisible(ReflectionMethod $method) {
        return $method->isPublic();
    }
}
