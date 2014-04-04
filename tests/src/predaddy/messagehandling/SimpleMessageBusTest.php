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

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use precore\util\UUID;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use RuntimeException;

require_once 'SimpleMessage.php';
require_once 'SimpleMessageHandler.php';
require_once 'AllMessageHandler.php';
require_once 'DeadMessageHandler.php';
require_once 'MultipleSameMessageHandler.php';
require_once 'PrioritizedHandler1.php';
require_once 'PrioritizedHandler2.php';

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
        $functionDescriptorFactory = new DefaultFunctionDescriptorFactory();
        $handlerDescriptorFactory = new AnnotatedMessageHandlerDescriptorFactory($functionDescriptorFactory);
        $this->bus = new SimpleMessageBus($handlerDescriptorFactory);
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

    public function testSuccessCallback()
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

    public function testFailedCallback()
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

    public function testInterceptorReset()
    {
        $interceptors = $this->getMock('Iterator');
        $interceptors
            ->expects(self::exactly(2))
            ->method('rewind');
        $this->bus->setInterceptors($interceptors);

        $this->bus->registerClosure(
            function (SimpleMessage $message) {
            }
        );

        $this->bus->post(new SimpleMessage());
        $this->bus->post(new SimpleMessage());
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
     * @expectedException RuntimeException
     */
    public function exceptionShouldThrownNonObjectMessage()
    {
        $this->bus->post(array());
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
        $order = array();
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
        self::assertSame(array(2, 1), $order);
    }

    /**
     * @test
     */
    public function prioritizedHandler()
    {
        $order = array();
        $handler = new PrioritizedHandler1($order);
        $this->bus->register($handler);
        $this->bus->post(new SimpleMessage());

        self::assertSame(array(2, 1), $order);
    }

    /**
     * @test
     */
    public function twoPrioritizedHandlers()
    {
        $order = array();
        $handler1 = new PrioritizedHandler1($order);
        $handler2 = new PrioritizedHandler2($order);
        $this->bus->register($handler1);
        $this->bus->register($handler2);
        $this->bus->post(new SimpleMessage());

        self::assertSame(array(6, 2, 1, -1), $order);
    }
}
