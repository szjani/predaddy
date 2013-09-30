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

namespace predaddy\messagehandling\interceptors;

use Exception;
use precore\lang\Object;
use predaddy\messagehandling\HandlerInterceptor;
use predaddy\messagehandling\InterceptorChain;
use predaddy\messagehandling\Message;
use trf4php\TransactionManager;

/**
 * Wraps the command dispatch process with transaction.
 *
 * @package predaddy\messagehandling\interceptors
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class TransactionInterceptor extends Object implements HandlerInterceptor
{
    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * @param TransactionManager $transactionManager
     */
    public function __construct(TransactionManager $transactionManager)
    {
        $this->transactionManager = $transactionManager;
    }

    public function invoke(Message $message, InterceptorChain $chain)
    {
        $this->transactionManager->beginTransaction();
        try {
            $res = $chain->proceed();
            $this->transactionManager->commit();
            return $res;
        } catch (Exception $e) {
            $this->transactionManager->rollback();
            self::getLogger()->warn("Transaction rollback invoked!");
            throw $e;
        }
    }
}
