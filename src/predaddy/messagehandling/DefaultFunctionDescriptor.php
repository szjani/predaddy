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
 * @author Szurovecz János <szjani@szjani.hu>
 */
class DefaultFunctionDescriptor extends Object implements FunctionDescriptor
{
    /**
     * @var ObjectClass
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
        if (!($object instanceof self)) {
            throw new ClassCastException(sprintf('Compared object must be an instance of %s', __CLASS__));
        }
        return $object->priority - $this->priority;
    }

    public function equals(ObjectInterface $object = null)
    {
        return $object instanceof self
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

    protected function canHandleValidMessage(ObjectClass $messageClass)
    {
        return $this->handledMessageClass->isAssignableFrom($messageClass);
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
            $deadMessage = ObjectClass::forName(DeadMessage::className())->isAssignableFrom($paramType);
            if (!$deadMessage) {
                return false;
            }
        }
        $this->handledMessageClass = ObjectClass::forName($paramType->getName());
        return true;
    }
}
