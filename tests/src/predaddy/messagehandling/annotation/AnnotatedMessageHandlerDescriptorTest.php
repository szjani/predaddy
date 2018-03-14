<?php
declare(strict_types=1);

namespace predaddy\messagehandling\annotation;

use PHPUnit\Framework\TestCase;
use predaddy\messagehandling\AllMessageHandler;
use predaddy\messagehandling\DefaultFunctionDescriptorFactory;
use predaddy\messagehandling\SimpleMessage;

/**
 * Description of EventHandlerConfigurationTest
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class AnnotatedMessageHandlerDescriptorTest extends TestCase
{
    /**
     * @var AnnotatedMessageHandlerDescriptor
     */
    private $config;

    public function setUp()
    {
        $handler = new AllMessageHandler();
        $this->config = new AnnotatedMessageHandlerDescriptor(
            $handler,
            new DefaultFunctionDescriptorFactory()
        );
    }

    public function testGetHandleMethodFor()
    {
        $message = new SimpleMessage();
        $methods = $this->config->getFunctionDescriptors();
        self::assertNotNull($methods);
        $counter = 0;
        foreach ($methods as $method) {
            if ($method->isHandlerFor($message)) {
                $counter++;
            }
        }
        self::assertEquals(1, $counter);
    }
}
