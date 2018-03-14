<?php
declare(strict_types=1);

namespace predaddy\messagehandling\util;

use DomainException;
use Exception;
use PHPUnit\Framework\TestCase;
use precore\util\UUID;
use predaddy\messagehandling\MessageBus;
use predaddy\messagehandling\MessageBusObjectMother;

/**
 * @package predaddy\messagehandling\util
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class MessageCallbackClosuresTest extends TestCase
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
