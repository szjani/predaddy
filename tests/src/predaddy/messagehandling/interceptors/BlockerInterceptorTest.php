<?php
declare(strict_types=1);

namespace predaddy\messagehandling\interceptors;

use PHPUnit\Framework\TestCase;
use precore\util\UUID;

/**
 * @package predaddy\messagehandling\interceptors
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class BlockerInterceptorTest extends TestCase
{
    private $message;

    /**
     * @var InterceptorChainSpy
     */
    private $chain;

    /**
     * @var BlockerInterceptor
     */
    private $interceptor;

    protected function setUp()
    {
        $this->message = UUID::randomUUID();
        $this->interceptor = new BlockerInterceptor();
        $this->chain = new InterceptorChainSpy();
    }

    public function testNonBlockingMode()
    {
        $this->interceptor->invoke($this->message, $this->chain);
        self::assertTrue($this->chain->calledTimes(1));
    }

    public function testBlockingMode()
    {
        $this->interceptor->startBlocking();
        $this->interceptor->invoke($this->message, $this->chain);
        self::assertTrue($this->chain->calledTimes(0));
        $this->interceptor->flush();
        self::assertTrue($this->chain->calledTimes(1));

        $this->interceptor->invoke($this->message, $this->chain);
        self::assertTrue($this->chain->calledTimes(1));
        $this->interceptor->flush();
        self::assertTrue($this->chain->calledTimes(2));

        $this->interceptor->startBlocking();
        $this->interceptor->invoke($this->message, $this->chain);
        self::assertTrue($this->chain->calledTimes(2));
        $this->interceptor->stopBlocking();
        $this->interceptor->invoke($this->message, $this->chain);
        self::assertTrue($this->chain->calledTimes(3));
        $this->interceptor->flush();
        self::assertTrue($this->chain->calledTimes(4));
    }

    public function testClearChains()
    {
        $this->interceptor->startBlocking();
        $this->interceptor->invoke($this->message, $this->chain);
        self::assertTrue($this->chain->calledTimes(0));
        $this->interceptor->clear();
        $this->interceptor->flush();
        self::assertTrue($this->chain->calledTimes(0));
    }

    /**
     * @test
     */
    public function shouldNotBlockNonBlockableMessage()
    {
        $aNonBlockable = $this->getMockBuilder('\predaddy\messagehandling\NonBlockable')->getMock();
        $this->interceptor->startBlocking();
        $this->interceptor->invoke($aNonBlockable, $this->chain);
        self::assertTrue($this->chain->calledTimes(1));
        $this->interceptor->flush();
        self::assertTrue($this->chain->calledTimes(1));
    }
}
