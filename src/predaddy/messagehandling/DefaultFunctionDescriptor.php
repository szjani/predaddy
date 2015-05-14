<?php
/*
 * Copyright (c) 2013 Janos Szurovecz
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

use precore\lang\ClassCastException;
use precore\lang\NullPointerException;
use precore\lang\Object;
use precore\lang\ObjectClass;
use precore\lang\ObjectInterface;
use precore\util\Objects;
use precore\util\Preconditions;
use ReflectionClass;

/**
 * Validates a function or a method whether it is a valid message handler or not.
 * The following rules must be followed the given function/method:
 *  - exactly one parameter
 *  - the parameter has typehint
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class DefaultFunctionDescriptor extends Object implements FunctionDescriptor
{
    /**
     * @var string
     */
    private $handledMessageClass = null;
    private $compatibleMessageClassNames = [];
    private $valid = false;

    /**
     * @var CallableWrapper
     */
    private $callableWrapper;

    /**
     * @var int
     */
    private $priority;

    public function __construct(CallableWrapper $callableWrapper, $priority)
    {
        $this->priority = (int) $priority;
        $this->callableWrapper = $callableWrapper;
        $this->valid = $this->check();
    }

    /**
     * @param object $message
     * @return boolean
     */
    public function isHandlerFor($message)
    {
        if (!$this->valid) {
            return false;
        }
        $messageClassName = get_class($message);
        if (!array_key_exists($messageClassName, $this->compatibleMessageClassNames)) {
            $this->compatibleMessageClassNames[$messageClassName] = $this->canHandleValidMessage($message);
        }
        return $this->compatibleMessageClassNames[$messageClassName];
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
        return $this->handledMessageClass;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param $object
     * @return int a negative integer, zero, or a positive integer
     *         as this object is less than, equal to, or greater than the specified object.
     * @throws ClassCastException - if the specified object's type prevents it from being compared to this object.
     * @throws NullPointerException if the specified object is null
     */
    public function compareTo($object)
    {
        Preconditions::checkNotNull($object, 'Compared object cannot be null');
        $this->getObjectClass()->cast($object);
        return $object->priority - $this->priority;
    }

    public function equals(ObjectInterface $object = null)
    {
        if ($object === $this) {
            return true;
        }
        /* @var $object self */
        return $object !== null
            && $this->getClassName() === $object->getClassName()
            && Objects::equal($this->callableWrapper, $object->callableWrapper)
            && Objects::equal($this->priority, $object->priority);
    }

    /**
     * @return CallableWrapper
     */
    public function getCallableWrapper()
    {
        return $this->callableWrapper;
    }

    protected function canHandleValidMessage($object)
    {
        return is_a($object, $this->handledMessageClass);
    }

    protected function getBaseMessageClassName()
    {
        return null;
    }

    private function check()
    {
        $params = $this->callableWrapper->reflectionFunction()->getParameters();
        if (count($params) !== 1) {
            return false;
        }
        /* @var $paramType ReflectionClass */
        $paramType = $params[0]->getClass();
        if ($paramType === null) {
            return false;
        }
        $baseClassName = $this->getBaseMessageClassName();
        $acceptableType = $baseClassName === null || ObjectClass::forName($baseClassName)->isAssignableFrom($paramType);
        if (!$acceptableType) {
            $deadMessage = DeadMessage::objectClass()->isAssignableFrom($paramType);
            if (!$deadMessage) {
                return false;
            }
        }
        $this->handledMessageClass = $paramType->getName();
        return true;
    }
}
