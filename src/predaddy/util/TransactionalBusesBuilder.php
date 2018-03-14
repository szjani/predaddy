<?php
declare(strict_types=1);

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
    public static function create(TransactionManager $txManager) : TransactionalBusesBuilder
    {
        return new self($txManager);
    }

    /**
     * @param DispatchInterceptor[] $interceptors
     * @return $this
     */
    public function interceptCommandsWithinTransaction(array $interceptors) : TransactionalBusesBuilder
    {
        $this->withinTransactionCommandInterceptors = $interceptors;
        return $this;
    }

    /**
     * @param DispatchInterceptor[] $interceptors
     * @return $this
     */
    public function interceptCommandsOutsideTransaction(array $interceptors) : TransactionalBusesBuilder
    {
        $this->outsideTransactionCommandInterceptors = $interceptors;
        return $this;
    }

    /**
     * @param DispatchInterceptor[] $interceptors
     * @return $this
     */
    public function interceptEventsWithinTransaction(array $interceptors) : TransactionalBusesBuilder
    {
        $this->withinTransactionEventInterceptors = $interceptors;
        return $this;
    }

    /**
     * @param DispatchInterceptor[] $interceptors
     * @return $this
     */
    public function interceptEventsOutsideTransaction(array $interceptors) : TransactionalBusesBuilder
    {
        $this->outsideTransactionEventInterceptors = $interceptors;
        return $this;
    }

    /**
     * This builder instance will create a {@link DirectCommandBus}
     * with the given {@link Repository}.
     *
     * @param Repository $repository
     * @return $this
     */
    public function withRepository(Repository $repository) : TransactionalBusesBuilder
    {
        $this->repository = $repository;
        $this->useDirectCommandBus();
        return $this;
    }

    /**
     * @param bool $flag If true, repository must be set
     * @return $this
     * @throws RuntimeException if flag is true and repository is not set
     */
    public function useDirectCommandBus($flag = true) : TransactionalBusesBuilder
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
    public function build() : TransactionalBuses
    {
        $commandBus = $this->createCommandBus();
        $eventBus = $this->createEventBus();
        EventPublisher::instance()->setEventBus($eventBus);
        return new TransactionalBuses($commandBus, $eventBus);
    }

    /**
     * @return EventBus
     */
    private function createEventBus() : EventBus
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
    private function createCommandBus() : CommandBus
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
