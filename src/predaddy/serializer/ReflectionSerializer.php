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

namespace predaddy\serializer;

use precore\lang\ObjectClass;
use precore\lang\ObjectInterface;
use ReflectionProperty;

/**
 * Uses reflection. Private variables in super classes are ignored!
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
final class ReflectionSerializer implements Serializer
{
    /**
     * @var array
     */
    private $blackListProperties;

    /**
     * @param string[] $blackListProperties
     */
    public function __construct(array $blackListProperties = [])
    {
        $this->blackListProperties = $blackListProperties;
    }

    /**
     * @param ObjectInterface $object
     * @return string
     */
    public function serialize(ObjectInterface $object)
    {
        $array = [];
        $properties = $object->getObjectClass()->getProperties();
        /* @var $property \ReflectionProperty */
        foreach ($properties as $property) {
            if ($this->isSerializable($property)) {
                $property->setAccessible(true);
                $array[$property->getName()] = $property->getValue($object);
            }
        }
        return serialize($array);
    }

    /**
     * @param string $serialized
     * @param ObjectClass $outputClass
     * @return ObjectInterface
     */
    public function deserialize($serialized, ObjectClass $outputClass)
    {
        $array = unserialize($serialized);
        $properties = $outputClass->getProperties();
        $result = $outputClass->newInstanceWithoutConstructor();
        /* @var $property \ReflectionProperty */
        foreach ($properties as $property) {
            if ($this->isSerializable($property) && array_key_exists($property->getName(), $array)) {
                $property->setAccessible(true);
                $property->setValue($result, $array[$property->getName()]);
            }
        }
        return $result;
    }

    protected function isSerializable(ReflectionProperty $property)
    {
        return !in_array($property->getName(), $this->blackListProperties);
    }
}
