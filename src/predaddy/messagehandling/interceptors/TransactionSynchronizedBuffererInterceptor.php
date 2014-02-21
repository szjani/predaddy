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

use InvalidArgumentException;
use precore\lang\Object;
use predaddy\messagehandling\HandlerInterceptor;
use predaddy\messagehandling\InterceptorChain;
use predaddy\messagehandling\Message;
use SplObjectStorage;
use trf4php\ObservableTransactionManager;
use trf4php\TransactionManagerObserver;

class TransactionSynchronizedBuffererInterceptor extends Object implements
    HandlerInterceptor,
    TransactionManagerObserver
{
    private $buffered = false;

    /**
     * @var SplObjectStorage
     */
    private $chains = array();

    /**
     * @var ObservableTransactionManager
     */
    private $transactionManager;

    /**
     * @param ObservableTransactionManager $transactionManager
     */
    public function __construct(ObservableTransactionManager $transactionManager)
    {
        $this->chains = new SplObjectStorage();
        $this->transactionManager = $transactionManager;
        $this->transactionManager->attach($this);
    }

    /**
     * @param Message $message
     * @param InterceptorChain $chain
     */
    public function invoke(Message $message, InterceptorChain $chain)
    {
        if (!$this->isBuffered()) {
            $chain->proceed();
        } else {
            $this->chains->attach($chain);
        }
    }

    /**
     * Observes transaction manager.
     *
     * @param ObservableTransactionManager $manager
     * @param string $event
     * @throws InvalidArgumentException
     */
    public function update(ObservableTransactionManager $manager, $event)
    {
        if ($manager !== $this->transactionManager) {
            throw new InvalidArgumentException('Must not be synchronized to multiple transaction managers');
        }
        switch ($event) {
            case ObservableTransactionManager::POST_BEGIN_TRANSACTION:
            case ObservableTransactionManager::PRE_TRANSACTIONAL:
                $this->startBuffer();
                break;
            case ObservableTransactionManager::POST_COMMIT:
            case ObservableTransactionManager::POST_TRANSACTIONAL:
                $this->flush();
                break;
            case ObservableTransactionManager::POST_ROLLBACK:
            case ObservableTransactionManager::ERROR_COMMIT:
            case ObservableTransactionManager::ERROR_ROLLBACK:
            case ObservableTransactionManager::ERROR_TRANSACTIONAL:
                $this->stopBuffer();
                break;
        }
    }

    protected function flush()
    {
        $chains = $this->chains;
        $this->stopBuffer();
        /* @var $chain InterceptorChain */
        foreach ($chains as $chain) {
            $chain->proceed();
        }
    }

    /**
     * @return boolean
     */
    public function isBuffered()
    {
        return $this->buffered;
    }

    protected function startBuffer()
    {
        $this->buffered = true;
    }

    protected function stopBuffer()
    {
        $this->buffered = false;
        $this->chains = new SplObjectStorage();
    }
}
