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

namespace predaddy\eventhandling;

use DateTime;
use precore\lang\Object;
use precore\lang\ObjectInterface;
use precore\util\UUID;

/**
 * Base class for all types of events. Contains the event identifier and timestamp.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
abstract class EventBase extends Object implements Event
{
    protected $id;
    protected $timestamp;

    public function __construct()
    {
        $this->timestamp = new DateTime();
        $this->id = UUID::randomUUID()->toString();
    }

    /**
     * @return string
     */
    public function getEventIdentifier()
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getTimestamp()
    {
        return clone $this->timestamp;
    }

    public function serialize()
    {
        $array = array();
        $properties = $this->getObjectClass()->getProperties();
        /* @var $property \ReflectionProperty */
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $array[$property->getName()] = $property->getValue($this);
        }
        return serialize($array);
    }

    public function unserialize($serialized)
    {
        $array = unserialize($serialized);
        $properties = $this->getObjectClass()->getProperties();
        /* @var $property \ReflectionProperty */
        foreach ($properties as $property) {
            if (array_key_exists($property->getName(), $array)) {
                $property->setAccessible(true);
                $property->setValue($this, $array[$property->getName()]);
            }
        }
    }

    public function equals(ObjectInterface $object = null)
    {
        return $object instanceof self && $this->id === $object->id;
    }
}
