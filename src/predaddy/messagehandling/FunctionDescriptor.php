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

use precore\lang\Comparable;
use precore\lang\ObjectClass;
use precore\lang\ObjectInterface;

/**
 * @package predaddy\messagehandling
 */
interface FunctionDescriptor extends Comparable, ObjectInterface
{
    /**
     * @return boolean
     */
    public function isValid();

    /**
     * @param ObjectClass $messageClass
     * @return boolean
     */
    public function isHandlerFor(ObjectClass $messageClass);

    /**
     * @return CallableWrapper
     */
    public function getCallableWrapper();

    /**
     * @return string
     */
    public function getHandledMessageClassName();

    /**
     * @return int
     */
    public function getPriority();
}
