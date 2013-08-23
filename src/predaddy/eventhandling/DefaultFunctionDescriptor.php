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

use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;

/**
 * Validates a function or a method whether it is a valid event handler or not.
 * The following rules must be followed the given function/method:
 *  - exactly one parameter
 *  - the parameter's typehint is a subclass of Event (including Event)
 *
 * @package predaddy\eventhandling
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class DefaultFunctionDescriptor implements FunctionDescriptor
{
    private $handledEventClassName = null;
    private $compatibleEventClassNames = array();
    private $valid = false;

    /**
     * @var ReflectionFunction
     */
    private $function;

    public function __construct(ReflectionFunctionAbstract $function)
    {
        $this->function = $function;
        $this->valid = $this->check();
    }

    /**
     * @param ReflectionClass $eventClass
     * @return boolean
     */
    public function isHandlerFor(ReflectionClass $eventClass)
    {
        if (!$this->valid) {
            return false;
        }
        $eventClassName = $eventClass->getName();
        if (!array_key_exists($eventClassName, $this->compatibleEventClassNames)) {
            $sameClass = $eventClass->getName() === $this->handledEventClassName;
            $subClass = $eventClass->isSubclassOf($this->handledEventClassName);
            $this->compatibleEventClassNames[$eventClassName] = $sameClass || $subClass;
        }
        return $this->compatibleEventClassNames[$eventClassName];
    }

    protected function check()
    {
        $eventClassName = __NAMESPACE__ . '\Event';
        $params = $this->function->getParameters();
        if (count($params) !== 1) {
            return false;
        }
        /* @var $paramType ReflectionClass */
        $paramType = $params[0]->getClass();
        if ($paramType === null
            || (!$paramType->isSubclassOf($eventClassName) && $paramType->getName() !== $eventClassName)) {
            return false;
        }
        $this->handledEventClassName = $paramType->getName();
        return true;
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * @return string
     */
    public function getHandledEventClassName()
    {
        return $this->handledEventClassName;
    }

    /**
     * @return ReflectionFunctionAbstract
     */
    public function getReflectionFunction()
    {
        return $this->function;
    }
}
