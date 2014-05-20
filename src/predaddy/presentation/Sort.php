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

namespace predaddy\presentation;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use precore\lang\Object;
use precore\lang\ObjectInterface;
use precore\util\Objects;

/**
 * Several Order instance can be stored in a Sort object.
 * It provides some methods to be able to handle Orders easily.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class Sort extends Object implements IteratorAggregate, Countable
{
    private $orders = [];

    /**
     * @param array $orders Array of Order objects
     */
    public function __construct(array $orders)
    {
        $this->orders = $orders;
    }

    /**
     * Factory method to order by several properties with the same direction.
     *
     * @param array $properties
     * @param Direction $direction
     * @return Sort
     */
    public static function create(array $properties, Direction $direction = null)
    {
        if ($direction === null) {
            $direction = Direction::$ASC;
        }
        $orders = [];
        foreach ($properties as $property) {
            $orders[] = new Order($direction, $property);
        }
        return new self($orders);
    }

    /**
     * Does not modifies the object itself,
     * will return a new instance instead.
     *
     * @param Sort $sort
     * @return Sort
     */
    public function andSort(Sort $sort = null)
    {
        if ($sort === null) {
            return $this;
        }
        $orders = $this->orders;
        foreach ($sort as $order) {
            $orders[] = $order;
        }
        return new Sort($orders);
    }

    /**
     * Returns the direction defined to the given property.
     *
     * @param string $property
     * @return Order
     */
    public function getOrderFor($property)
    {
        /* @var $order Order */
        foreach ($this as $order) {
            if ($order->getProperty() == $property) {
                return $order;
            }
        }
        return null;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->orders);
    }

    public function count()
    {
        return count($this->orders);
    }

    public function equals(ObjectInterface $object = null)
    {
        if ($object === $this) {
            return true;
        }
        if (!($object instanceof self)) {
            return false;
        }
        return $this->orders === $object->orders;
    }

    public function toString()
    {
        return Objects::toStringHelper($this)
            ->add('orders', $this->orders)
            ->toString();
    }
}
