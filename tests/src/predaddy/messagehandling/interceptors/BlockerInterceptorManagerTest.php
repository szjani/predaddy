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
use predaddy\messagehandling\SubscriberExceptionContext;
use RuntimeException;

/**
 * @package predaddy\messagehandling\interceptors
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class BlockerInterceptorManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var BlockerInterceptorManager
     */
    private $blockerManager;

    /**
     * @var BlockerInterceptor
     */
    private $blocker;

    /**
     * @var InterceptorChainSpy
     */
    private $mainChain;

    /**
     * @var InterceptorChainSpy
     */
    private $managedChain;

    private $message;

    protected function setUp()
    {
        $this->message = UUID::randomUUID();
        $this->mainChain = $this->getMock('\predaddy\messagehandling\InterceptorChain');
        $this->managedChain = new InterceptorChainSpy();
        $this->blocker = new BlockerInterceptor();
        $this->blockerManager = new BlockerInterceptorManager($this->blocker);
    }

    /**
     * @test
     */
    public function allChainsAreProceed()
    {
        $this->mainChain
            ->expects(self::once())
            ->method('proceed')
            ->will(
                self::returnCallback(
                    function () {
                        $this->blocker->invoke($this->message, $this->managedChain);
                    }
                )
            );
        $this->blockerManager->invoke($this->message, $this->mainChain);
        self::assertTrue($this->managedChain->calledTimes(1));
    }

    /**
     * @test
     */
    public function chainsMustBeClearedIfExceptionOccur()
    {
        $bus = $this->getMock('\predaddy\messagehandling\MessageBus');
        $wrapper = $this->getMock('\predaddy\messagehandling\CallableWrapper');
        $context = new SubscriberExceptionContext($bus, $this->message, $wrapper);
        $this->mainChain
            ->expects(self::once())
            ->method('proceed')
            ->will(
                self::returnCallback(
                    function () use ($context) {
                        $this->blocker->invoke($this->message, $this->managedChain);
                        $this->blockerManager->handleException(
                            new RuntimeException('Expected exception'),
                            $context
                        );
                    }
                )
            );
        $this->blockerManager->invoke($this->message, $this->mainChain);
        self::assertTrue($this->managedChain->calledTimes(0));
    }
}
