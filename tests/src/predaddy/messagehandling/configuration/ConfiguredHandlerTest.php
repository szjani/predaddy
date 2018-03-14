<?php

namespace predaddy\messagehandling\configuration;

use PHPUnit\Framework\TestCase;
use precore\lang\ObjectInterface;
use precore\util\UUID;
use predaddy\messagehandling\AbstractMessageHandler;
use predaddy\messagehandling\DefaultFunctionDescriptorFactory;
use predaddy\messagehandling\SimpleMessageBus;
use predaddy\messagehandling\SimpleMessageHandler;
use predaddy\messagehandling\util\MessageCallbackClosures;
use stdClass;

/**
 * Class ConfiguredHandlerTest
 *
 * @package predaddy\messagehandling\configuration
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ConfiguredHandlerTest extends TestCase
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
            ->withMethod(__CLASS__, 'handleUuid')
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
            ->withMethod(__CLASS__, 'handleUuid')
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
            ->withMethod(__CLASS__, 'handleUuid')
            ->withMethod(__CLASS__, 'handleObject')
            ->build();
        $bus = $this->createBus($config);
        $bus->register($this);
        $message = UUID::randomUUID();
        $bus->post($message, self::assertReturn($message));
        self::assertEquals(2, $this->called);
    }

    /**
     * @test
     */
    public function shouldHandlerBeCalledFromParent()
    {
        $message = UUID::randomUUID();
        $handler = new SimpleMessageHandler();

        $config = Configuration::builder()
            ->withMethod(AbstractMessageHandler::className(), 'handleInParent')
            ->build();
        $bus = $this->createBus($config);
        $bus->register($handler);
        $bus->post($message);

        self::assertSame($message, $handler->lastParentMessage);
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
        return SimpleMessageBus::builder()
            ->withHandlerDescriptorFactory(
                new ConfiguredMessageHandlerDescriptorFactory(
                    new DefaultFunctionDescriptorFactory(),
                    $configuration
                )
            )
            ->build();
    }
}
