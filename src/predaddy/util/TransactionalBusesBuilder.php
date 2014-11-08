<?php
/*
 * Copyright (c) 2012-2014 Janos Szurovecz
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

namespace predaddy\util;

use precore\util\Preconditions;
use predaddy\commandhandling\CommandBus;
use predaddy\commandhandling\DirectCommandBus;
use predaddy\domain\EventPublisher;
use predaddy\domain\Repository;
use predaddy\eventhandling\EventBus;
use predaddy\messagehandling\DispatchInterceptor;
use predaddy\messagehandling\interceptors\BlockerInterceptor;
use predaddy\messagehandling\interceptors\BlockerInterceptorManager;
use predaddy\messagehandling\interceptors\ExceptionHandlerDelegate;
use predaddy\messagehandling\interceptors\WrapInTransactionInterceptor;
use RuntimeException;
use trf4php\TransactionManager;

/**
 * Class TransactionalBusesBuilder
 *
 * @package predaddy\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class TransactionalBusesBuilder
{
    /**
     * @var DispatchInterceptor[]
     */
    private $outsideTransactionCommandInterceptors = [];

    /**
     * @var DispatchInterceptor[]
     */
    private $withinTransactionCommandInterceptors = [];

    /**
     * @var DispatchInterceptor[]
     */
    private $outsideTransactionEventInterceptors = [];

    /**
     * @var DispatchInterceptor[]
     */
    private $withinTransactionEventInterceptors = [];

    /**
     * @var boolean
     */
    private $useDirectCommandBus = false;

    /**
     * @var BlockerInterceptor
     */
    private $blockerInterceptor;

    /**
     * @var BlockerInterceptorManager
     */
    private $blockerIntManager;

    /**
     * @var WrapInTransactionInterceptor
     */
    private $txInterceptor;

    /**
     * @var ExceptionHandlerDelegate
     */
    private $exceptionHandler;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @param TransactionManager $txManager
     */
    private function __construct(TransactionManager $txManager)
    {
        $this->blockerInterceptor = new BlockerInterceptor();
        $this->blockerIntManager = $this->blockerInterceptor->manager();
        $this->txInterceptor = new WrapInTransactionInterceptor($txManager);
        $this->exceptionHandler = new ExceptionHandlerDelegate([$this->txInterceptor, $this->blockerIntManager]);
    }

    /**
     * @param TransactionManager $txManager
     * @return TransactionalBusesBuilder
     */
    public static function create(TransactionManager $txManager)
    {
        return new self($txManager);
    }

    /**
     * @param DispatchInterceptor[] $interceptors
     * @return $this
     */
    public function interceptCommandsWithinTransaction(array $interceptors)
    {
        $this->withinTransactionCommandInterceptors = $interceptors;
        return $this;
    }

    /**
     * @param DispatchInterceptor[] $interceptors
     * @return $this
     */
    public function interceptCommandsOutsideTransaction(array $interceptors)
    {
        $this->outsideTransactionCommandInterceptors = $interceptors;
        return $this;
    }

    /**
     * @param DispatchInterceptor[] $interceptors
     * @return $this
     */
    public function interceptEventsWithinTransaction(array $interceptors)
    {
        $this->withinTransactionEventInterceptors = $interceptors;
        return $this;
    }

    /**
     * @param DispatchInterceptor[] $interceptors
     * @return $this
     */
    public function interceptEventsOutsideTransaction(array $interceptors)
    {
        $this->outsideTransactionEventInterceptors = $interceptors;
        return $this;
    }

    /**
     * @param Repository $repository
     * @return $this
     */
    public function withRepository(Repository $repository)
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * @param bool $flag If true, repository must be set
     * @return $this
     * @throws RuntimeException if flag is true and repository is not set
     */
    public function useDirectCommandBus($flag = true)
    {
        Preconditions::checkState(
            !$flag || $this->repository !== null,
            'You must set a Repository to be able to create DirectCommandBus'
        );
        $this->useDirectCommandBus = $flag;
        return $this;
    }

    /**
     * @return TransactionalBuses
     */
    public function build()
    {
        $commandBus = $this->createCommandBus();
        $eventBus = $this->createEventBus();
        EventPublisher::instance()->setEventBus($eventBus);
        return new TransactionalBuses($commandBus, $eventBus);
    }

    /**
     * @return EventBus
     */
    private function createEventBus()
    {
        return EventBus::builder()
            ->withInterceptors(
                array_merge(
                    $this->withinTransactionEventInterceptors,
                    [$this->blockerInterceptor],
                    $this->outsideTransactionEventInterceptors
                )
            )
            ->build();
    }

    /**
     * @return CommandBus|DirectCommandBus
     */
    private function createCommandBus()
    {
        $commandInterceptorList = array_merge(
            $this->outsideTransactionCommandInterceptors,
            [$this->blockerIntManager, $this->txInterceptor],
            $this->withinTransactionCommandInterceptors
        );
        return $this->useDirectCommandBus
            ? DirectCommandBus::builder($this->repository)
                ->withInterceptors($commandInterceptorList)
                ->withExceptionHandler($this->exceptionHandler)
                ->build()
            : CommandBus::builder()
                ->withInterceptors($commandInterceptorList)
                ->withExceptionHandler($this->exceptionHandler)
                ->build();
    }
}
