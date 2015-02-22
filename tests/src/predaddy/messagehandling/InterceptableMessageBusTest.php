<?php
/*
 * Copyright (c) 2015 Janos Szurovecz
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace predaddy\messagehandling;

use Exception;
use PHPUnit_Framework_TestCase;
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
class InterceptableMessageBusTest extends PHPUnit_Framework_TestCase
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
