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

namespace predaddy\messagehandling;

use precore\lang\ObjectClass;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;

/**
 * Validates a function or a method whether it is a valid message handler or not.
 * The following rules must be followed the given function/method:
 *  - exactly one parameter
 *  - the parameter has typehint
 *
 * @package predaddy\messagehandling\functiondescriptor
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class DefaultFunctionDescriptor implements FunctionDescriptor
{
    /**
     * @var ObjectClass
     */
    private $handledMessageClass = null;
    private $compatibleMessageClassNames = [];
    private $valid = false;

    /**
     * @var ReflectionFunction
     */
    private $function;

    /**
     * @var int
     */
    private $priority;

    public function __construct(ReflectionFunctionAbstract $function, $priority)
    {
        $this->function = $function;
        $this->priority = (int) $priority;
        $this->valid = $this->check();
    }

    /**
     * @param ObjectClass $messageClass
     * @return boolean
     */
    public function isHandlerFor(ObjectClass $messageClass)
    {
        if (!$this->valid) {
            return false;
        }
        $messageClassName = $messageClass->getName();
        if (!array_key_exists($messageClassName, $this->compatibleMessageClassNames)) {
            $this->compatibleMessageClassNames[$messageClassName] = $this->canHandleValidMessage($messageClass);
        }
        return $this->compatibleMessageClassNames[$messageClassName];
    }

    protected function canHandleValidMessage(ObjectClass $messageClass)
    {
        return $this->handledMessageClass->isAssignableFrom($messageClass);
    }

    protected function getBaseMessageClassName()
    {
        return null;
    }

    protected function check()
    {
        $baseClassName = $this->getBaseMessageClassName();
        $params = $this->function->getParameters();
        if (count($params) !== 1) {
            return false;
        }
        /* @var $paramType ReflectionClass */
        $paramType = $params[0]->getClass();
        if ($paramType === null) {
            return false;
        }
        $deadMessage = ObjectClass::forName(DeadMessage::className())->isAssignableFrom($paramType);
        $acceptableType = $baseClassName === null || ObjectClass::forName($baseClassName)->isAssignableFrom($paramType);
        if (!($deadMessage || $acceptableType)) {
            return false;
        }
        $this->handledMessageClass = ObjectClass::forName($paramType->getName());
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
    public function getHandledMessageClassName()
    {
        return $this->handledMessageClass->getName();
    }

    /**
     * @return ReflectionFunctionAbstract
     */
    public function getReflectionFunction()
    {
        return $this->function;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }
}
