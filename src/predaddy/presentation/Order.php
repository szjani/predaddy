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

namespace predaddy\presentation;

use precore\lang\Object;
use precore\lang\ObjectInterface;
use precore\util\Objects;

/**
 * Represents an ordering for the given property with the given direction.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class Order extends Object
{
    private $direction;
    private $property;

    /**
     * @param Direction $direction
     * @param string $property
     */
    public function __construct(Direction $direction, $property)
    {
        $this->direction = $direction;
        $this->property = $property;
    }

    /**
     * @return Direction
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    public function equals(ObjectInterface $object = null)
    {
        if ($object === $this) {
            return true;
        }
        if (!($object instanceof self)) {
            return false;
        }
        return $this->direction->equals($object->direction) && $this->property === $object->property;
    }

    public function toString()
    {
        return Objects::toStringHelper($this)
            ->add('property', $this->property)
            ->add('direction', $this->direction)
            ->toString();
    }
}
