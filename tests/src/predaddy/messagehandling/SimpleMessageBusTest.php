<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use precore\util\UUID;
use predaddy\fixture\PropagationStoppableMessage;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\util\MessageCallbackClosures;
use RuntimeException;

/**
 * Description of DirectEventBusTest
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class SimpleMessageBusTest extends TestCase
{
    /**
     * @var SimpleMessageBus
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

        self::assertSame($expectedMessage, $deadMessageHandler->lastMessage->wrappedMessage());
    }

    /**
     * @test
     */
    public function shouldHandlerBeCalledFromParent()
    {
        $message = UUID::randomUUID();
        $handler = new SimpleMessageHandler();

        $this->bus->register($handler);
        $this->bus->post($message);

        self::assertSame($message, $handler->lastParentMessage);
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
        $callback = $this->getMockBuilder('\predaddy\messagehandling\MessageCallback')->getMock();
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
        $callback = $this->getMockBuilder('\predaddy\messagehandling\MessageCallback')->getMock();
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

        $exceptionHandler = $this->getMockBuilder('predaddy\messagehandling\SubscriberExceptionHandler')->getMock();
        $exceptionHandler
            ->expects(self::once())
            ->method('handleException')
            ->with($exception);

        $bus = SimpleMessageBus::builder()
            ->withExceptionHandler($exceptionHandler)
            ->build();
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
        $exceptionHandler = $this->getMockBuilder('predaddy\messagehandling\SubscriberExceptionHandler')->getMock();
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
        $bus = SimpleMessageBus::builder()
            ->withExceptionHandler($exceptionHandler)
            ->build();
        $bus->registerClosure(
            function (UUID $msg) use ($exception) {
                throw $exception;
            }
        );

        $callback = $this->getMockBuilder('\predaddy\messagehandling\MessageCallback')->getMock();
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
        $bus = new SimpleMessageBus();
        $bus->registerClosure(
            function (UUID $msg) use ($exception) {
                throw $exception;
            }
        );

        $callback = $this->getMockBuilder('\predaddy\messagehandling\MessageCallback')->getMock();
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

    public function testRegisterHandlerFactory()
    {
        $handler = new SimpleMessageHandler();
        $factory = function (Message $message) use ($handler) {
            return $handler;
        };
        $this->bus->registerHandlerFactory($factory);
        $message = new SimpleMessage();
        $this->bus->post($message);
        self::assertSame($message, $handler->lastMessage);
    }

    public function testUnregisterHandlerFactory()
    {
        $handler = new SimpleMessageHandler();
        $factory = function (Message $message) use ($handler) {
            return $handler;
        };
        $this->bus->registerHandlerFactory($factory);
        $this->bus->unregisterHandlerFactory($factory);
        $message = new SimpleMessage();
        $this->bus->post($message);
        self::assertNull($handler->lastMessage);
    }

    /**
     * @test
     */
    public function shouldStopPropagation()
    {
        $called = 0;
        $handler1 = function (PropagationStoppableMessage $msg) use (&$called) {
            $msg->stopPropagation();
            $called++;
        };
        $handler2 = function (PropagationStoppableMessage $msg) use (&$called) {
            $called++;
        };
        $this->bus->registerClosure($handler1, 2);
        $this->bus->registerClosure($handler2, 1);
        $this->bus->post(new PropagationStoppableMessage());
        self::assertEquals(1, $called);
    }

    /**
     * @test
     */
    public function shouldNotHandleAlreadyPropagationStoppedMessage()
    {
        $called = false;
        $deadMessageSent = false;
        $handler = function (PropagationStoppableMessage $msg) use (&$called) {
            $called = true;
        };
        $deadMessageHandler = function (DeadMessage $msg) use (&$deadMessageSent) {
            $deadMessageSent = true;
        };
        $this->bus->registerClosure($handler);
        $this->bus->registerClosure($deadMessageHandler);

        $msg = new PropagationStoppableMessage();
        $msg->stopPropagation();
        $this->bus->post($msg);
        self::assertFalse($called);
        self::assertFalse($deadMessageSent);
    }
}
