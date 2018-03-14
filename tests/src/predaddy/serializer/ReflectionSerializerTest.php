<?php
declare(strict_types=1);

namespace predaddy\serializer;

use PHPUnit\Framework\TestCase;

class ReflectionSerializerTest extends TestCase
{
    public function testSerialize()
    {
        $serializer = new ReflectionSerializer();
        $obj = new Bar();
        $serialized = $serializer->serialize($obj);
        /* @var $newObj Bar */
        $newObj = $serializer->deserialize($serialized, Bar::objectClass());

        self::assertEquals($obj->getBarPrivate(), $newObj->getBarPrivate());
        self::assertEquals($obj->getBarProtected(), $newObj->getBarProtected());
        self::assertEquals($obj->getBarPublic(), $newObj->getBarPublic());

        self::assertEquals($obj->getFooPublic(), $newObj->getFooPublic());
        self::assertEquals($obj->getFooProtected(), $newObj->getFooProtected());
        self::assertNotEquals($obj->getFooPrivate(), $newObj->getFooPrivate());
        self::assertNull($newObj->getFooPrivate());
    }
}
