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

namespace predaddy\messagehandling\interceptors;

use PHPUnit_Framework_TestCase;
use precore\util\UUID;

/**
 * @package predaddy\messagehandling\interceptors
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class BlockerInterceptorTest extends PHPUnit_Framework_TestCase
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
        $aNonBlockable = $this->getMock('\predaddy\messagehandling\NonBlockable');
        $this->interceptor->startBlocking();
        $this->interceptor->invoke($aNonBlockable, $this->chain);
        self::assertTrue($this->chain->calledTimes(1));
        $this->interceptor->flush();
        self::assertTrue($this->chain->calledTimes(1));
    }
}
