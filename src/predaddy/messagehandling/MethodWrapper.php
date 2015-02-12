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

use precore\lang\Object;
use precore\lang\ObjectInterface;
use precore\util\Objects;
use ReflectionMethod;

final class MethodWrapper extends Object implements CallableWrapper
{
    /**
     * @var object
     */
    private $object;

    /**
     * @var ReflectionMethod
     */
    private $method;

    public function __construct($object, ReflectionMethod $method)
    {
        $this->object = $object;
        $this->method = $method;
    }

    public function invoke($message)
    {
        return $this->method->invoke($this->object, $message);
    }

    public function toString()
    {
        return Objects::toStringHelper($this)
            ->add('objectClass', get_class($this->object))
            ->add('methodName', $this->method->getName())
            ->toString();
    }

    public function equals(ObjectInterface $object = null)
    {
        if ($object === $this) {
            return true;
        }
        return $object instanceof MethodWrapper
            && Objects::equal($this->object, $object->object)
            && Objects::equal($this->method, $object->method);
    }

    /**
     * @return \ReflectionFunctionAbstract
     */
    public function reflectionFunction()
    {
        return $this->method;
    }
}
