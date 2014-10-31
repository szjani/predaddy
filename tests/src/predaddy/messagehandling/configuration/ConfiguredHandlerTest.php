<?php

namespace predaddy\messagehandling\configuration;

use PHPUnit_Framework_TestCase;
use precore\lang\ObjectInterface;
use precore\util\UUID;
use predaddy\messagehandling\DefaultFunctionDescriptorFactory;
use predaddy\messagehandling\SimpleMessageBus;
use predaddy\messagehandling\util\MessageCallbackClosures;
use stdClass;

/**
 * Class ConfiguredHandlerTest
 *
 * @package predaddy\messagehandling\configuration
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ConfiguredHandlerTest extends PHPUnit_Framework_TestCase
{
    private $called = 0;

    private static function assertReturn($expected)
    {
        return MessageCallbackClosures::builder()
            ->successClosure(
                function ($returnValue) use (&$expected) {
                    self::assertSame($expected, $returnValue);
                }
            )
            ->build();
    }

    protected function setUp()
    {
        $this->called = 0;
    }

    /**
     * @test
     */
    public function shouldNotHandle()
    {
        $config = Configuration::builder()
            ->withMethod(__CLASS__, new MethodConfiguration('handleUuid'))
            ->build();
        $bus = $this->createBus($config);
        $bus->register($this);
        $message = new stdClass();
        $bus->post($message);
        self::assertEquals(0, $this->called);
    }

    /**
     * @test
     */
    public function shouldCallHandler()
    {
        $config = Configuration::builder()
            ->withMethod(__CLASS__, new MethodConfiguration('handleUuid'))
            ->build();
        $bus = $this->createBus($config);
        $bus->register($this);
        $message = UUID::randomUUID();
        $bus->post($message, self::assertReturn($message));
        self::assertEquals(1, $this->called);
    }

    /**
     * @test
     */
    public function shouldHandleInterfaceAsWell()
    {
        $config = Configuration::builder()
            ->withMethod(__CLASS__, new MethodConfiguration('handleUuid'))
            ->withMethod(__CLASS__, new MethodConfiguration('handleObject'))
            ->build();
        $bus = $this->createBus($config);
        $bus->register($this);
        $message = UUID::randomUUID();
        $bus->post($message, self::assertReturn($message));
        self::assertEquals(2, $this->called);
    }

    /**
     * Handler method
     *
     * @param UUID $uuid
     * @return UUID
     */
    public function handleUuid(UUID $uuid)
    {
        $this->called++;
        return $uuid;
    }

    /**
     * Handler method
     *
     * @param ObjectInterface $object
     * @return ObjectInterface
     */
    public function handleObject(ObjectInterface $object)
    {
        $this->called++;
        return $object;
    }

    /**
     * @param Configuration $configuration
     * @return SimpleMessageBus
     */
    private function createBus(Configuration $configuration)
    {
        return new SimpleMessageBus(
            SimpleMessageBus::DEFAULT_NAME,
            [],
            null,
            new ConfiguredMessageHandlerDescriptorFactory(
                new DefaultFunctionDescriptorFactory(),
                $configuration
            )
        );
    }
}
