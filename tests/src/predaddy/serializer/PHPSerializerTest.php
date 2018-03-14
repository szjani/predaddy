<?php
declare(strict_types=1);

namespace predaddy\serializer;

use PHPUnit\Framework\TestCase;

/**
 * @package src\predaddy\serializer
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class PHPSerializerTest extends TestCase
{
    /**
     * @var PHPSerializer
     */
    private $serializer;

    protected function setUp()
    {
        $this->serializer = new PHPSerializer();
    }

    /**
     * @test
     */
    public function deserializationShouldWork()
    {
        $bar = new Bar();
        $serialized = $this->serializer->serialize($bar);
        self::assertEquals($bar, $this->serializer->deserialize($serialized, Bar::objectClass()));
    }
}
