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

namespace predaddy\util;

use predaddy\commandhandling\CommandBus;
use predaddy\commandhandling\DirectCommandBus;
use predaddy\domain\EventPublisher;
use predaddy\domain\Repository;
use predaddy\eventhandling\EventBus;
use predaddy\messagehandling\interceptors\BlockerInterceptor;
use predaddy\messagehandling\interceptors\BlockerInterceptorManager;
use predaddy\messagehandling\interceptors\TransactionalExceptionHandler;
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
     * @var array
     */
    private $commandInterceptors = [];

    /**
     * @var array
     */
    private $eventInterceptors = [];

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
     * @var TransactionalExceptionHandler
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
        $this->exceptionHandler = new TransactionalExceptionHandler($this->txInterceptor, $this->blockerIntManager);
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
     * @param array $interceptors
     * @return $this
     */
    public function withCommandInterceptors(array $interceptors)
    {
        $this->commandInterceptors = $interceptors;
        return $this;
    }

    /**
     * @param array $interceptors
     * @return $this
     */
    public function withEventInterceptors(array $interceptors)
    {
        $this->eventInterceptors = $interceptors;
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
        if ($flag && $this->repository === null) {
            throw new RuntimeException('You must set a Repository to be able to create DirectCommandBus');
        }
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
        return new EventBus(
            EventBus::DEFAULT_NAME,
            array_merge($this->eventInterceptors, [$this->blockerInterceptor])
        );
    }

    /**
     * @return CommandBus|DirectCommandBus
     */
    private function createCommandBus()
    {
        $commandBus = null;
        $commandInterceptorList = array_merge([$this->blockerIntManager, $this->txInterceptor], $this->commandInterceptors);
        if ($this->useDirectCommandBus) {
            $commandBus = new DirectCommandBus(
                $this->repository,
                CommandBus::DEFAULT_NAME,
                $commandInterceptorList,
                $this->exceptionHandler
            );
        } else {
            $commandBus = new CommandBus(
                CommandBus::DEFAULT_NAME,
                $commandInterceptorList,
                $this->exceptionHandler
            );
        }
        return $commandBus;
    }
}
