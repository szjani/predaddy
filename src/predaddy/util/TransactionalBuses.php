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
use predaddy\commandhandling\CommandFunctionDescriptorFactory;
use predaddy\commandhandling\DirectCommandBus;
use predaddy\domain\EventPublisher;
use predaddy\domain\impl\RegisterableRepositoryRepository;
use predaddy\domain\RepositoryRepository;
use predaddy\eventhandling\EventBus;
use predaddy\eventhandling\EventFunctionDescriptorFactory;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\interceptors\BlockerInterceptor;
use predaddy\messagehandling\interceptors\TransactionalExceptionHandler;
use predaddy\messagehandling\interceptors\WrapInTransactionInterceptor;
use predaddy\messagehandling\SimpleMessageBusFactory;
use trf4php\TransactionManager;

/**
 * Utility class which simplifies the EventBus and CommandBus creation process.
 *
 * @package predaddy\messagehandling\util
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
final class TransactionalBuses
{
    private $commandBus;
    private $eventBus;

    final private function __construct()
    {
    }

    /**
     * @param TransactionManager $tmManager
     * @param RepositoryRepository $repositoryRepository
     * @param array $commandInterceptors
     * @param array $eventInterceptors
     * @return TransactionalBuses
     */
    public static function create(
        TransactionManager $tmManager,
        RepositoryRepository $repositoryRepository = null,
        array $commandInterceptors = [],
        array $eventInterceptors = []
    ) {
        if ($repositoryRepository === null) {
            $repositoryRepository = new RegisterableRepositoryRepository();
        }
        $result = new static();
        /* @var $result TransactionalBuses */
        $blockerInterceptor = new BlockerInterceptor();
        $blockerIntManager = $blockerInterceptor->manager();
        $trInterceptor = new WrapInTransactionInterceptor($tmManager);
        $exceptionHandler = new TransactionalExceptionHandler($trInterceptor, $blockerIntManager);

        $cmdHandlerDescFact = new AnnotatedMessageHandlerDescriptorFactory(
            new CommandFunctionDescriptorFactory()
        );
        $result->commandBus = new DirectCommandBus(
            $repositoryRepository,
            new SimpleMessageBusFactory($cmdHandlerDescFact),
            $cmdHandlerDescFact,
            array_merge([$trInterceptor, $blockerIntManager], $commandInterceptors),
            $exceptionHandler
        );
        $result->eventBus = new EventBus(
            new AnnotatedMessageHandlerDescriptorFactory(new EventFunctionDescriptorFactory()),
            array_merge($eventInterceptors, [$blockerInterceptor])
        );
        EventPublisher::instance()->setEventBus($result->eventBus);
        return $result;
    }

    /**
     * @return CommandBus
     */
    final public function commandBus()
    {
        return $this->commandBus;
    }

    /**
     * @return EventBus
     */
    final public function eventBus()
    {
        return $this->eventBus;
    }
}
