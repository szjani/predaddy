<?php
declare(strict_types=1);

namespace predaddy\messagehandling\interceptors;

use Exception;
use lf4php\MDC;
use precore\lang\BaseObject;
use precore\util\UUID;
use predaddy\messagehandling\DispatchInterceptor;
use predaddy\messagehandling\InterceptorChain;
use predaddy\messagehandling\SubscriberExceptionContext;
use predaddy\messagehandling\SubscriberExceptionHandler;
use trf4php\TransactionManager;

/**
 * Wraps the command dispatch process in transaction. It handles nested levels, which means it does not
 * start a new transaction if there is an open one.
 *
 * <p> It puts a transaction ID to MDC with 'TR' key.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class WrapInTransactionInterceptor extends BaseObject implements DispatchInterceptor, SubscriberExceptionHandler
{
    const MDC_KEY = 'TR';

    /**
     * @var TransactionManager
     */
    private $transactionManager;

    private $transactionLevel = 0;

    private $rollbackRequired = false;

    /**
     * @param TransactionManager $transactionManager
     */
    public function __construct(TransactionManager $transactionManager)
    {
        $this->transactionManager = $transactionManager;
    }

    public function invoke($message, InterceptorChain $chain) : void
    {
        try {
            $this->transactionLevel++;
            if ($this->transactionLevel == 1) {
                $this->transactionManager->beginTransaction();
                MDC::put(self::MDC_KEY, UUID::randomUUID()->toString());
                self::getLogger()->debug('Transaction has been started');
                $chain->proceed();
                if ($this->rollbackRequired) {
                    $this->transactionManager->rollback();
                    $this->rollbackRequired = false;
                    self::getLogger()->debug('Transaction has been rolled back');
                } else {
                    $this->transactionManager->commit();
                    self::getLogger()->debug('Transaction has been committed');
                }
                MDC::remove(self::MDC_KEY);
            } else {
                self::getLogger()->debug('Transaction has already started, using the existing one');
                $chain->proceed();
            }
            $this->transactionLevel--;
        } catch (Exception $e) {
            MDC::remove(self::MDC_KEY);
            $this->transactionLevel--;
            throw $e;
        }
    }

    public function handleException(Exception $exception, SubscriberExceptionContext $context) : void
    {
        $this->rollbackRequired = true;
        self::getLogger()->debug("Transaction rollback required, context '{}'!", [$context], $exception);
    }

    /**
     * @return int
     */
    public function getTransactionLevel() : int
    {
        return $this->transactionLevel;
    }
}
