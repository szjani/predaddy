<?php

namespace predaddy\messagehandling\interceptors;

use PHPUnit_Framework_TestCase;
use predaddy\messagehandling\SimpleMessage;

/**
 * Class WrapInTransactionInterceptorTest
 *
 * @package predaddy\messagehandling\interceptors
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class WrapInTransactionInterceptorTest extends PHPUnit_Framework_TestCase
{
    private $transactionManager;

    /**
     * @var WrapInTransactionInterceptor
     */
    private $interceptor;

    private $aMessage;

    private $aChain;

    protected function setUp()
    {
        $this->aMessage = new SimpleMessage();
        $this->aChain = $this->getMock('\predaddy\messagehandling\InterceptorChain');
        $this->transactionManager = $this->getMock('\trf4php\TransactionManager');
        $this->interceptor = new WrapInTransactionInterceptor($this->transactionManager);
    }

    /**
     * @test
     */
    public function shouldOmitIfTransactionIsAlreadyStarted()
    {
        $this->transactionManager
            ->expects(self::once())
            ->method('beginTransaction');
        $this->transactionManager
            ->expects(self::once())
            ->method('commit');
        $startTransactionChain = $this->getMock('\predaddy\messagehandling\InterceptorChain');
        $startTransactionChain
            ->expects(self::once())
            ->method('proceed')
            ->will(
                self::returnCallback(
                    function () {
                        $this->interceptor->invoke($this->aMessage, $this->aChain);
                    }
                )
            );
        $this->interceptor->invoke($this->aMessage, $startTransactionChain);
        self::assertEquals(0, $this->interceptor->getTransactionLevel());
    }
}
