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

use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;

/**
 * Validates a function or a method whether it is a valid message handler or not.
 * The following rules must be followed the given function/method:
 *  - exactly one parameter
 *  - the parameter's typehint is a subclass of Message (including Message)
 *
 * @package predaddy\messagehandling\functiondescriptor
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class DefaultFunctionDescriptor implements FunctionDescriptor
{
    private $handledMessageClassName = null;
    private $compatibleMessageClassNames = array();
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
     * @param Message $message
     * @return boolean
     */
    public function isHandlerFor(Message $message)
    {
        if (!$this->valid) {
            return false;
        }
        $messageClassName = $message->getClassName();
        if (!array_key_exists($messageClassName, $this->compatibleMessageClassNames)) {
            $this->compatibleMessageClassNames[$messageClassName] = $this->canHandleValidMessage($message);
        }
        return $this->compatibleMessageClassNames[$messageClassName];
    }

    protected function canHandleValidMessage(Message $message)
    {
        $messageClass = $message->getObjectClass();
        $sameClass = $messageClass->getName() === $this->handledMessageClassName;
        $subClass = $messageClass->isSubclassOf($this->handledMessageClassName);
        return $sameClass || $subClass;
    }

    protected function getBaseMessageClassName()
    {
        return __NAMESPACE__ . '\Message';
    }

    protected function check()
    {
        $messageClassName = $this->getBaseMessageClassName();
        $params = $this->function->getParameters();
        if (count($params) !== 1) {
            return false;
        }
        /* @var $paramType ReflectionClass */
        $paramType = $params[0]->getClass();
        if ($paramType === null
            || (!$paramType->isSubclassOf($messageClassName) && $paramType->getName() !== $messageClassName)) {
            return false;
        }
        $this->handledMessageClassName = $paramType->getName();
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
        return $this->handledMessageClassName;
    }

    /**
     * @return ReflectionFunctionAbstract
     */
    public function getReflectionFunction()
    {
        return $this->function;
    }
}
