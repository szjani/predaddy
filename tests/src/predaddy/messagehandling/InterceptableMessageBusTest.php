<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use Exception;
use PHPUnit\Framework\TestCase;
use predaddy\commandhandling\CommandBus;
use predaddy\domain\EventPublisher;
use predaddy\eventhandling\EventBus;
use predaddy\fixture\BaseEvent;
use predaddy\fixture\BaseEvent2;
use predaddy\fixture\SimpleCommand;
use predaddy\messagehandling\interceptors\BlockerInterceptor;

/**
 * Class BusFactoryTest
 *
 * @package predaddy\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class InterceptableMessageBusTest extends TestCase
{
    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @var CommandBus
     */
    private $commandBus;

    protected function setUp()
    {
        $blockerInterceptor = new BlockerInterceptor();
        $this->eventBus = EventBus::builder()
            ->withInterceptors([$blockerInterceptor])
            ->build();
        $blockerInterceptorManager = $blockerInterceptor->manager();
        $this->commandBus = CommandBus::builder()
            ->withInterceptors([$blockerInterceptorManager])
            ->withExceptionHandler($blockerInterceptorManager)
            ->build();
    }

    /**
     * @test
     */
    public function shouldBlockBothEvents()
    {
        EventPublisher::instance()->setEventBus($this->eventBus);
        $eventHandlerCalled = 0;

        $eventHandler1 = function (BaseEvent $event) use (&$eventHandlerCalled) {
            $eventHandlerCalled++;
        };
        $eventHandler2 = function (BaseEvent2 $event) use (&$eventHandlerCalled) {
            $eventHandlerCalled++;
        };
        $this->eventBus->registerClosure($eventHandler1);
        $this->eventBus->registerClosure($eventHandler2);

        $commandHandler = function (SimpleCommand $command) {
            EventPublisher::instance()->post(new BaseEvent());
            EventPublisher::instance()->post(new BaseEvent2());
            throw new Exception();
        };
        $this->commandBus->registerClosure($commandHandler);

        $this->commandBus->post(new SimpleCommand(null, null, null));
        self::assertEquals(0, $eventHandlerCalled);
    }
}
