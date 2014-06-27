<?php
/*
 * Copyright (c) 2012-2014 Szurovecz János
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

namespace predaddy\domain;

use precore\lang\Object;
use precore\lang\ObjectInterface;
use precore\util\Objects;

class DefaultAggregateId extends Object implements AggregateId
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $aggregateClass;

    /**
     * @param string $value
     * @param string $aggregateClass
     */
    public function __construct($value, $aggregateClass)
    {
        $this->value = $value;
        $this->aggregateClass = $aggregateClass;
    }

    /**
     * @return string
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function aggregateClass()
    {
        return $this->aggregateClass;
    }

    public function toString()
    {
        return Objects::toStringHelper($this)
            ->add('value', $this->value)
            ->add('aggregateClass', $this->aggregateClass)
            ->toString();
    }

    public function equals(ObjectInterface $object = null)
    {
        return $object instanceof AggregateId
            && $this->value() == $object->value()
            && $this->aggregateClass() == $object->aggregateClass();
    }
}
