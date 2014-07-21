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

use Exception;
use precore\lang\Object;
use predaddy\messagehandling\SubscriberExceptionContext;
use predaddy\messagehandling\SubscriberExceptionHandler;

/**
 * @author Szurovecz János <szjani@szjani.hu>
 */
final class TransactionalExceptionHandler extends Object implements SubscriberExceptionHandler
{
    /**
     * @var WrapInTransactionInterceptor
     */
    private $transactionInterceptor;
    /**
     * @var BlockerInterceptorManager
     */
    private $blockerInterceptorManager;

    public function __construct(
        WrapInTransactionInterceptor $transactionInt,
        BlockerInterceptorManager $blockerIntManager
    ) {
        $this->transactionInterceptor = $transactionInt;
        $this->blockerInterceptorManager = $blockerIntManager;
    }

    public function handleException(Exception $exception, SubscriberExceptionContext $context)
    {
        $this->transactionInterceptor->handleException($exception, $context);
        $this->blockerInterceptorManager->handleException($exception, $context);
    }
}
