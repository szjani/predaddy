<?php
declare(strict_types=1);

namespace predaddy\messagehandling\interceptors;

use Exception;
use PHPUnit\Framework\TestCase;
use predaddy\messagehandling\AbstractMessage;
use predaddy\messagehandling\SimpleMessageBus;
use stdClass;

/**
 * Class WrapInTransactionInterceptorTest
 *
 * @package predaddy\messagehandling\interceptors
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class WrapInTransactionInterceptorTest extends TestCase
{
    /**
     * @var DummyTransactionManager
     */
    private $transactionManager;

    /**
     * @var WrapInTransactionInterceptor
     */
    private $interceptor;

    /**
     * @var SimpleMessageBus
     */
    private $aMessageBus;

    protected function setUp()
    {
        $this->transactionManager = new DummyTransactionManager();
        $this->interceptor = new WrapInTransactionInterceptor($this->transactionManager);
        $this->aMessageBus = SimpleMessageBus::builder()
            ->withInterceptors([$this->interceptor])
            ->withExceptionHandler($this->interceptor)
            ->build();
    }

    /**
     * @test
     */
    public function shouldOmitIfTransactionIsAlreadyStarted()
    {
        $storedObject1 = new stdClass();
        $storedObject2 = new stdClass();
        $this->aMessageBus->registerClosure(
            function (Cmd1 $cmd) use ($storedObject1) {
                $this->aMessageBus->post(new Cmd2());
                $this->transactionManager->store($storedObject1);
            }
        );
        $this->aMessageBus->registerClosure(
            function (Cmd2 $cmd) use ($storedObject2) {
                $this->transactionManager->store($storedObject2);
            }
        );
        $this->aMessageBus->post(new Cmd1());
        self::assertEquals(0, $this->interceptor->getTransactionLevel());
        self::assertTrue($this->transactionManager->isCommitted($storedObject1));
        self::assertTrue($this->transactionManager->isCommitted($storedObject2));
    }

    /**
     * @test
     */
    public function shouldRollbackInFirstLevel()
    {
        $storedObject1 = new stdClass();
        $storedObject2 = new stdClass();
        $this->aMessageBus->registerClosure(
            function (Cmd1 $cmd) use ($storedObject1) {
                $this->aMessageBus->post(new Cmd2());
                $this->transactionManager->store($storedObject1);
            }
        );
        $this->aMessageBus->registerClosure(
            function (Cmd2 $cmd) use ($storedObject2) {
                $this->transactionManager->store($storedObject2);
                throw new Exception("Expected exception");
            }
        );
        $this->aMessageBus->post(new Cmd1());
        self::assertEquals(0, $this->interceptor->getTransactionLevel());
        self::assertFalse($this->transactionManager->isCommitted($storedObject1));
        self::assertFalse($this->transactionManager->isCommitted($storedObject2));
    }
}

class Cmd1 extends AbstractMessage {
}
class Cmd2 extends AbstractMessage {
}
