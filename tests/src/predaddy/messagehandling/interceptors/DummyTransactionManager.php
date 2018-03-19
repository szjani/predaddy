<?php
declare(strict_types=1);

namespace predaddy\messagehandling\interceptors;

use Closure;
use Exception;
use RuntimeException;
use SplObjectStorage;
use trf4php\TransactionException;
use trf4php\TransactionManager;

/**
 * Class DummyTransactionManager
 *
 * @package predaddy\messagehandling\interceptors
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class DummyTransactionManager implements TransactionManager
{
    /**
     * @var SplObjectStorage
     */
    private $pending;

    /**
     * @var SplObjectStorage
     */
    private $committed;

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        $this->pending = new SplObjectStorage();
        $this->committed = new SplObjectStorage();
    }

    public function store($object)
    {
        $this->pending->attach($object);
    }

    public function isCommitted($object)
    {
        return $this->committed->contains($object);
    }

    /**
     * Begin a transaction.
     */
    public function beginTransaction() : void
    {
        $this->init();
    }

    /**
     * Commit a transaction.
     *
     * @throws TransactionException
     */
    public function commit() : void
    {
        $this->committed = $this->pending;
        $this->pending = new SplObjectStorage();
    }

    /**
     * Rollback a transaction.
     *
     * @throws TransactionException
     */
    public function rollback() : void
    {
        $this->init();
    }

    /**
     * Wrapper method, $func will be executed inside a transaction.
     *
     * @param Closure $func
     * @return mixed The return value of $func
     * @throws Exception Throwed by $func
     * @throws TransactionException
     */
    public function transactional(Closure $func)
    {
        throw new RuntimeException('Does not support this method');
    }
}
 