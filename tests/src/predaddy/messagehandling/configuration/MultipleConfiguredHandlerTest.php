<?php

namespace predaddy\messagehandling\configuration;

use PHPUnit\Framework\TestCase;
use precore\util\UUID;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\DefaultFunctionDescriptorFactory;
use predaddy\messagehandling\SimpleMessageBus;

/**
 * Class MultipleConfiguredHandlerTest
 *
 * @package predaddy\messagehandling\configuration
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class MultipleConfiguredHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function shouldWorkBothAnnotationAndConfiguration()
    {
        $configuration = Configuration::builder()
            ->withMethod(ConfiguredHandler::className(), 'configuredHandler')
            ->build();
        $bus = $this->createBus($configuration);
        $handler = new ConfiguredHandler();
        $bus->register($handler);
        $message = UUID::randomUUID();
        $bus->post($message);
        self::assertSame($message, $handler->lastParentMessage);
        self::assertSame($message, $handler->messageOverConfiguredHandler);
    }

    /**
     * @test
     */
    public function shouldCallOnlyOnceAHandlerMethod()
    {
        $configuration = Configuration::builder()
            ->withMethod(ConfiguredHandler::className(), 'handleInParent')
            ->build();
        $bus = $this->createBus($configuration);
        $handler = new ConfiguredHandler();
        $bus->register($handler);
        $message = UUID::randomUUID();
        $bus->post($message);
        self::assertSame($message, $handler->lastParentMessage);
        self::assertEquals(1, $handler->counterInParent);
    }

    /**
     * @param Configuration $configuration
     * @return SimpleMessageBus
     */
    private function createBus(Configuration $configuration)
    {
        $functionDescFactory = new DefaultFunctionDescriptorFactory();
        return SimpleMessageBus::builder()
            ->withHandlerDescriptorFactory(
                new MultipleMessageHandlerDescriptorFactory(
                    $functionDescFactory,
                    [
                        new ConfiguredMessageHandlerDescriptorFactory($functionDescFactory, $configuration),
                        new AnnotatedMessageHandlerDescriptorFactory($functionDescFactory)
                    ]
                )
            )
            ->build();
    }
}
