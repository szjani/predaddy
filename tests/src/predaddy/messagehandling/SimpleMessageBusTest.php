<?php
/*
 * Copyright (c) 2013 Szurovecz János
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
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use precore\util\UUID;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\util\MessageCallbackClosures;
use RuntimeException;

/**
 * Description of DirectEventBusTest
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class SimpleMessageBusTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MessageBus
     */
    protected $bus;

    public function setUp()
    {
        $this->bus = MessageBusObjectMother::createAnnotatedBus();
    }

    public function testTwoHandlerPost()
    {
        $message1 = new SimpleMessage();
        $message2 = new SimpleMessage();

        $simpleMessageHandler = new SimpleMessageHandler();
        $allMessageHandler = new AllMessageHandler();
        $this->bus->register($simpleMessageHandler);
        $this->bus->register($allMessageHandler);

        $this->bus->post($message1);

        self::assertSame($message1, $simpleMessageHandler->lastMessage);
        self::assertSame($message1, $allMessageHandler->lastMessage);

        $this->bus->unregister($simpleMessageHandler);
        $this->bus->unregister($allMessageHandler);
        $this->bus->post($message2);
        self::assertSame($message1, $simpleMessageHandler->lastMessage);
        self::assertSame($message1, $allMessageHandler->lastMessage);
    }

    public function testDeadMessageHandling()
    {
        $expectedMessage = $message = new SimpleMessage();
        $deadMessageHandler = new DeadMessageHandler();

        $this->bus->register($deadMessageHandler);
        $this->bus->post($message);

        self::assertSame($expectedMessage, $deadMessageHandler->lastMessage->getMessage());
    }

    public function testMultipleSaveMessageHandler()
    {
        $multipleSameMessageHandler = new MultipleSameMessageHandler();
        $this->bus->register($multipleSameMessageHandler);

        $message = new SimpleMessage();
        $this->bus->post($message);

        self::assertSame($message, $multipleSameMessageHandler->lastMessage);
        self::assertSame($message, $multipleSameMessageHandler->lastMessage2);
        self::assertSame($message, $multipleSameMessageHandler->lastMessage3);
        self::assertSame($message, $multipleSameMessageHandler->lastMessage4);

        $message2 = new DeadMessage($message);
        $this->bus->post($message2);

        self::assertSame($message, $multipleSameMessageHandler->lastMessage);
        self::assertSame($message2, $multipleSameMessageHandler->lastMessage2);
        self::assertSame($message, $multipleSameMessageHandler->lastMessage3);
        self::assertSame($message2, $multipleSameMessageHandler->lastMessage4);
    }

    public function testSameHandlerClassMultipleTimes()
    {
        $message = new SimpleMessage();

        $simpleMessageHandler1 = new SimpleMessageHandler();
        $simpleMessageHandler2 = new SimpleMessageHandler();
        $this->bus->register($simpleMessageHandler1);
        $this->bus->register($simpleMessageHandler2);

        $this->bus->post($message);

        self::assertSame($message, $simpleMessageHandler1->lastMessage);
        self::assertSame($message, $simpleMessageHandler2->lastMessage);
    }

    /**
     * @test
     */
    public function successCallback()
    {
        $message = new SimpleMessage();
        $called = false;
        $this->bus->registerClosure(
            function (SimpleMessage $message) use (&$called) {
                $called = true;
                return $message->data;
            }
        );
        $callback = $this->getMock('\predaddy\messagehandling\MessageCallback');
        $callback
            ->expects(self::once())
            ->method('onSuccess')
            ->with($message->data);
        $this->bus->post($message, $callback);
        self::assertTrue($called);
    }

    /**
     * @test
     */
    public function failedCallback()
    {
        $message = new SimpleMessage();
        $called = false;
        $e = new InvalidArgumentException('expected');
        $this->bus->registerClosure(
            function (SimpleMessage $message) use (&$called, $e) {
                $called = true;
                throw $e;
            }
        );
        $callback = $this->getMock('\predaddy\messagehandling\MessageCallback');
        $callback
            ->expects(self::once())
            ->method('onFailure')
            ->with($e);
        $this->bus->post($message, $callback);
        self::assertTrue($called);
    }

    /**
     * @test
     */
    public function dispatchNonMessageObject()
    {
        $called = false;
        $this->bus->registerClosure(
            function (UUID $msg) use (&$called) {
                $called = true;
            }
        );
        $this->bus->post(UUID::randomUUID());
        self::assertTrue($called);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function exceptionShouldThrownNonObjectMessage()
    {
        $this->bus->post([]);
    }

    /**
     * @test
     */
    public function noDeadMessageIfHandlerFails()
    {
        $deadMessagePosted = false;
        $this->bus->registerClosure(
            function (DeadMessage $msg) use (&$deadMessagePosted) {
                $deadMessagePosted = true;
            }
        );
        $this->bus->registerClosure(
            function (UUID $msg) {
                throw new RuntimeException('Expected exception');
            }
        );
        $this->bus->post(UUID::randomUUID());
        self::assertFalse($deadMessagePosted);
    }

    /**
     * @test
     */
    public function prioritizedClosures()
    {
        $order = [];
        $this->bus->registerClosure(
            function (Message $msg) use (&$order) {
                $order[] = 1;
            },
            1
        );
        $this->bus->registerClosure(
            function (Message $msg) use (&$order) {
                $order[] = 2;
            },
            2
        );

        $this->bus->post(new SimpleMessage());
        self::assertSame([2, 1], $order);
    }

    /**
     * @test
     */
    public function prioritizedHandler()
    {
        $order = [];
        $handler = new PrioritizedHandler1($order);
        $this->bus->register($handler);
        $this->bus->post(new SimpleMessage());

        self::assertSame([2, 1], $order);
    }

    /**
     * @test
     */
    public function twoPrioritizedHandlers()
    {
        $order = [];
        $handler1 = new PrioritizedHandler1($order);
        $handler2 = new PrioritizedHandler2($order);
        $this->bus->register($handler1);
        $this->bus->register($handler2);
        $this->bus->post(new SimpleMessage());

        self::assertSame([6, 2, 1, -1], $order);
    }

    /**
     * @test
     */
    public function exceptionInDeadMessageHandlerIsBeingPassedToCallback()
    {
        $deadMessageCalled = false;
        $this->bus->registerClosure(
            function (DeadMessage $deadMessage) use (&$deadMessageCalled) {
                $deadMessageCalled = true;
                throw new RuntimeException('Excepted exception');
            }
        );
        $exceptionPassed = false;
        $callback = MessageCallbackClosures::builder()
            ->failureClosure(
                function (Exception $exp) use (&$exceptionPassed) {
                    $exceptionPassed = true;
                }
            )
            ->build();
        $this->bus->post(UUID::randomUUID(), $callback);
        self::assertTrue($exceptionPassed);
    }

    /**
     * @test
     */
    public function nonReturnValueMustNotBePassedToCallback()
    {
        $this->bus->registerClosure(
            function (UUID $msg) {
            }
        );
        $onSuccessCalled = 0;
        $callback = MessageCallbackClosures::builder()
            ->successClosure(
                function ($result) use (&$onSuccessCalled) {
                    $onSuccessCalled++;
                }
            )
            ->build();
        $this->bus->post(UUID::randomUUID(), $callback);
        self::assertEquals(0, $onSuccessCalled);
    }

    /**
     * @test
     */
    public function exceptionHandlerMustBeCalled()
    {
        $exception = new RuntimeException('Expected exception');

        $exceptionHandler = $this->getMock('predaddy\messagehandling\SubscriberExceptionHandler');
        $exceptionHandler
            ->expects(self::once())
            ->method('handleException')
            ->with($exception);

        $bus = new SimpleMessageBus(
            new AnnotatedMessageHandlerDescriptorFactory(
                new DefaultFunctionDescriptorFactory()
            ),
            [],
            $exceptionHandler
        );
        $bus->registerClosure(
            function (UUID $msg) use ($exception) {
                throw $exception;
            }
        );
        $bus->post(UUID::randomUUID());
    }

    /**
     * @test
     */
    public function exceptionThrownByExceptionHandlerMustBeCaught()
    {
        $exception = new RuntimeException('Expected exception');
        $exceptionHandler = $this->getMock('predaddy\messagehandling\SubscriberExceptionHandler');
        $exceptionHandler
            ->expects(self::once())
            ->method('handleException')
            ->will(
                self::returnCallback(
                    function () {
                        throw new RuntimeException('Expected exception');
                    }
                )
            );
        $bus = new SimpleMessageBus(
            new AnnotatedMessageHandlerDescriptorFactory(
                new DefaultFunctionDescriptorFactory()
            ),
            [],
            $exceptionHandler
        );
        $bus->registerClosure(
            function (UUID $msg) use ($exception) {
                throw $exception;
            }
        );

        $callback = $this->getMock('\predaddy\messagehandling\MessageCallback');
        $callback
            ->expects(self::once())
            ->method('onFailure')
            ->with($exception);

        $bus->post(UUID::randomUUID(), $callback);
    }

    /**
     * @test
     */
    public function exceptionThrownByCallbackMustBeCaught()
    {
        $exception = new RuntimeException('Expected exception');
        $bus = new SimpleMessageBus(
            new AnnotatedMessageHandlerDescriptorFactory(
                new DefaultFunctionDescriptorFactory()
            )
        );
        $bus->registerClosure(
            function (UUID $msg) use ($exception) {
                throw $exception;
            }
        );

        $callback = $this->getMock('\predaddy\messagehandling\MessageCallback');
        $callback
            ->expects(self::once())
            ->method('onFailure')
            ->will(
                self::returnCallback(
                    function (Exception $e) use ($exception) {
                        self::assertSame($exception, $e);
                        throw new RuntimeException('Expected exception');
                    }
                )
            );

        $bus->post(UUID::randomUUID(), $callback);
    }
}
