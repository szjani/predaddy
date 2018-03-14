<?php
declare(strict_types=1);

namespace predaddy\serializer;

use JMS\Serializer\SerializerInterface;
use precore\lang\ObjectClass;
use precore\lang\ObjectInterface;

final class JmsSerializer implements Serializer
{
    /**
     * @var SerializerInterface
     */
    private $jmsSerializer;

    /**
     * @var string
     */
    private $format;

    /**
     * @param SerializerInterface $jmsSerializer
     * @param string $format xml|json|yaml
     */
    public function __construct(SerializerInterface $jmsSerializer, string $format)
    {
        $this->jmsSerializer = $jmsSerializer;
        $this->format = $format;
    }

    /**
     * @param ObjectInterface $object
     * @return string
     */
    public function serialize(ObjectInterface $object) : string
    {
        return $this->jmsSerializer->serialize($object, $this->format);
    }

    /**
     * @param string $serialized
     * @param ObjectClass $outputClass
     * @return ObjectInterface
     */
    public function deserialize($serialized, ObjectClass $outputClass) : ObjectInterface
    {
        return $this->jmsSerializer->deserialize($serialized, $outputClass->getName(), $this->format);
    }
}
