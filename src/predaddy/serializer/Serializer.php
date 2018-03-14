<?php
declare(strict_types=1);

namespace predaddy\serializer;

use precore\lang\ObjectClass;
use precore\lang\ObjectInterface;

interface Serializer
{
    /**
     * @param ObjectInterface $object
     * @return string
     */
    public function serialize(ObjectInterface $object) : string;

    /**
     * @param string $serialized
     * @param ObjectClass $outputClass
     * @return ObjectInterface
     */
    public function deserialize($serialized, ObjectClass $outputClass) : ObjectInterface;
}
