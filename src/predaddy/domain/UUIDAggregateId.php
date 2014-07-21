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

use precore\util\Objects;
use precore\lang\Object;
use precore\lang\ObjectInterface;
use precore\util\UUID;

/**
 * Provides an easy way to generate unique IDs.
 *
 * It can be extended and only the aggregateClass() method must be implemented.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
abstract class UUIDAggregateId extends Object implements AggregateId
{
    /**
     * @var string
     */
    private $uuid;

    final private function __construct($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return static
     */
    final public static function create()
    {
        return new static(UUID::randomUUID()->toString());
    }

    /**
     * @param string $value
     * @return static
     */
    final public static function from($value)
    {
        return new static($value);
    }

    /**
     * @return string
     */
    final public function value()
    {
        return $this->uuid;
    }

    final public function equals(ObjectInterface $object = null)
    {
        return $object instanceof AggregateId
            && $this->value() === $object->value()
            && $this->aggregateClass() === $object->aggregateClass();
    }

    final public function toString()
    {
        return Objects::toStringHelper($this)
            ->add('uuid', $this->value())
            ->add('aggregateClass', $this->aggregateClass())
            ->toString();
    }
}
