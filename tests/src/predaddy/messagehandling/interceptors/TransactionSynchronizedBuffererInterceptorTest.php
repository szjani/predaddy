<?php
/*
 * Copyright (c) 2014 Szurovecz János
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
use trf4php\ObservableTransactionManager;

class TransactionSynchronizedBuffererInterceptorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var TransactionSynchronizedBuffererInterceptor
     */
    private $interceptor;

    /**
     * @var ObservableTransactionManager
     */
    private $transactionManager;

    public function setUp()
    {
        $this->transactionManager = $this->getMock('\trf4php\ObservableTransactionManager');
        $this->interceptor = new TransactionSynchronizedBuffererInterceptor($this->transactionManager);
    }

    public function testWithoutSynchronization()
    {
        $message = $this->getMock('\predaddy\messagehandling\Message');
        $chain = $this->getMock('\predaddy\messagehandling\InterceptorChain');
        $chain
            ->expects(self::once())
            ->method('proceed');
        $this->interceptor->invoke($message, $chain);
    }

    public function testWithTransactionAndRollback()
    {
        $message = $this->getMock('\predaddy\messagehandling\Message');
        $chain = $this->getMock('\predaddy\messagehandling\InterceptorChain');
        $chain
            ->expects(self::never())
            ->method('proceed');
        $this->interceptor->update($this->transactionManager, ObservableTransactionManager::POST_BEGIN_TRANSACTION);
        $this->interceptor->invoke($message, $chain);
        $this->interceptor->update($this->transactionManager, ObservableTransactionManager::POST_ROLLBACK);
    }

    public function testWithTransactionAndCommit()
    {
        $message = $this->getMock('\predaddy\messagehandling\Message');
        $chain = $this->getMock('\predaddy\messagehandling\InterceptorChain');
        $chain
            ->expects(self::once())
            ->method('proceed');
        $this->interceptor->update($this->transactionManager, ObservableTransactionManager::POST_BEGIN_TRANSACTION);
        $this->interceptor->invoke($message, $chain);
        $this->interceptor->update($this->transactionManager, ObservableTransactionManager::POST_COMMIT);
    }

    public function testWithTwoTransactionsButLastSuccessful()
    {
        $message1 = $this->getMock('\predaddy\messagehandling\Message');
        $chain1 = $this->getMock('\predaddy\messagehandling\InterceptorChain');
        $chain1
            ->expects(self::never())
            ->method('proceed');

        $message2 = $this->getMock('\predaddy\messagehandling\Message');
        $chain2 = $this->getMock('\predaddy\messagehandling\InterceptorChain');
        $chain2
            ->expects(self::once())
            ->method('proceed');

        $this->interceptor->update($this->transactionManager, ObservableTransactionManager::POST_BEGIN_TRANSACTION);
        $this->interceptor->invoke($message1, $chain1);
        $this->interceptor->update($this->transactionManager, ObservableTransactionManager::POST_ROLLBACK);

        $this->interceptor->update($this->transactionManager, ObservableTransactionManager::POST_BEGIN_TRANSACTION);
        $this->interceptor->invoke($message2, $chain2);
        $this->interceptor->update($this->transactionManager, ObservableTransactionManager::POST_COMMIT);
    }
}
