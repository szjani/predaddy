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

namespace predaddy\messagehandling\util;

use DomainException;
use Exception;
use PHPUnit_Framework_TestCase;
use precore\util\UUID;
use predaddy\messagehandling\MessageBus;
use predaddy\messagehandling\MessageBusObjectMother;

/**
 * @package predaddy\messagehandling\util
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class MessageCallbackClosuresTest extends PHPUnit_Framework_TestCase
{
    public $successCalled;
    public $failureCalled;
    private $expectedResult = 'Hello World';
    private $expectedException;
    private $successClosure;
    private $failureClosure;
    private $message;
    private $handlerWithResult;
    private $handlerWithException;

    /**
     * @var MessageBus
     */
    private $bus;

    protected function setUp()
    {
        $this->message = UUID::randomUUID();
        $message = $this->message;
        $this->successCalled = false;
        $this->failureCalled = false;
        $self = $this;
        $expectedResult = $this->expectedResult;
        $this->successClosure = function ($result) use ($self, $expectedResult) {
            MessageCallbackClosuresTest::assertSame($expectedResult, $result);
            $self->successCalled = true;
        };

        $this->expectedException = new DomainException('Expected exception');
        $expectedException = $this->expectedException;
        $this->failureClosure = function (Exception $exp) use ($self, $expectedException) {
            MessageCallbackClosuresTest::assertSame($expectedException, $exp);
            $self->failureCalled = true;
        };

        $this->handlerWithResult = function (UUID $msg) use ($message, $expectedResult) {
            MessageCallbackClosuresTest::assertSame($message, $msg);
            return $expectedResult;
        };
        $this->handlerWithException = function (UUID $msg) use ($message, $expectedException) {
            MessageCallbackClosuresTest::assertSame($message, $msg);
            throw $expectedException;
        };

        $this->bus = MessageBusObjectMother::createAnnotatedBus();
    }

    /**
     * @test
     */
    public function onlySuccessClosureDefined()
    {
        $callback = MessageCallbackClosures::builder()
            ->successClosure($this->successClosure)
            ->build();

        $this->bus->registerClosure($this->handlerWithResult);
        $this->bus->registerClosure($this->handlerWithException);
        $this->bus->post($this->message, $callback);
        self::assertTrue($this->successCalled);
        self::assertFalse($this->failureCalled);
    }

    /**
     * @test
     */
    public function onlyFailureClosureDefined()
    {
        $callback = MessageCallbackClosures::builder()
            ->failureClosure($this->failureClosure)
            ->build();

        $this->bus->registerClosure($this->handlerWithResult);
        $this->bus->registerClosure($this->handlerWithException);
        $this->bus->post($this->message, $callback);
        self::assertFalse($this->successCalled);
        self::assertTrue($this->failureCalled);
    }

    /**
     * @test
     */
    public function closuresMustBeCalled()
    {
        $callback = MessageCallbackClosures::builder()
            ->successClosure($this->successClosure)
            ->failureClosure($this->failureClosure)
            ->build();

        $this->bus->registerClosure($this->handlerWithResult);
        $this->bus->registerClosure($this->handlerWithException);
        $this->bus->post($this->message, $callback);
        self::assertTrue($this->successCalled);
        self::assertTrue($this->failureCalled);
    }
}
