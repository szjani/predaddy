<?php
/*
 * Copyright (c) 2012-2014 Szurovecz János
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

namespace predaddy\messagehandling\monitoring;

use ArrayIterator;
use DomainException;
use Exception;
use PHPUnit_Framework_TestCase;
use precore\util\UUID;
use predaddy\messagehandling\MessageBus;
use predaddy\messagehandling\MessageBusObjectMother;
use predaddy\messagehandling\util\MessageCallbackClosures;

/**
 * @package predaddy\messagehandling\monitoring
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class MonitoringTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MessageBus
     */
    private $messageBus;

    /**
     * @var MessageBus
     */
    private $errorMessageBus;

    protected function setUp()
    {
        $this->messageBus = MessageBusObjectMother::createAnnotatedBus();
        $this->errorMessageBus = MessageBusObjectMother::createAnnotatedBus();
        $this->messageBus->setInterceptors(
            new ArrayIterator(
                array(
                    new MonitoringInterceptor($this->errorMessageBus)
                )
            )
        );
    }

    /**
     * @test
     */
    public function errorEventMustBeSent()
    {
        $messageCounter = 0;
        $message = UUID::randomUUID();
        $exception = new DomainException("Expected exception");

        $this->messageBus->registerClosure(
            function (UUID $msg) use ($exception, $message, &$messageCounter) {
                $messageCounter++;
                MonitoringTest::assertSame($message, $msg);
                throw $exception;
            }
        );

        $errorCounter = 0;
        $this->errorMessageBus->registerClosure(
            function (ErrorMessage $msg) use ($message, $exception, &$errorCounter) {
                MonitoringTest::assertSame($message, $msg->getMessage());
                MonitoringTest::assertSame($exception, $msg->getException());
                $errorCounter++;
            }
        );

        $callbackCalled = false;
        $callback = MessageCallbackClosures::builder()
            ->failureClosure(
                function (Exception $exp) use (&$callbackCalled, $exception) {
                    $callbackCalled = true;
                    MonitoringTest::assertSame($exception, $exp);
                }
            )
            ->build();
        $this->messageBus->post($message, $callback);
        self::assertEquals(1, $messageCounter);
        self::assertEquals(1, $errorCounter);
        self::assertTrue($callbackCalled);
    }

    /**
     * @test
     */
    public function returnTheHandlerReturnValue()
    {
        $message = UUID::randomUUID();

        $this->messageBus->registerClosure(
            function (UUID $msg) use ($message) {
                MonitoringTest::assertSame($message, $msg);
                return $message;
            }
        );

        $called = false;
        $callback = MessageCallbackClosures::builder()
            ->successClosure(
                function ($result) use ($message, &$called) {
                    MonitoringTest::assertSame($message, $result);
                    $called = true;
                }
            )
            ->build();
        $this->messageBus->post($message, $callback);
        self::assertTrue($called);
    }
}
