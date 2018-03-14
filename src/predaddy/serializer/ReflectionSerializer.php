<?php
declare(strict_types=1);

namespace predaddy\serializer;

use precore\lang\ObjectClass;
use precore\lang\ObjectInterface;
use ReflectionProperty;

/**
 * Uses reflection. Private variables in super classes are ignored!
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
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
    public function serialize(ObjectInterface $object) : string
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
    public function deserialize($serialized, ObjectClass $outputClass) : ObjectInterface
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

    protected function isSerializable(ReflectionProperty $property) : bool
    {
        return !in_array($property->getName(), $this->blackListProperties);
    }
}
