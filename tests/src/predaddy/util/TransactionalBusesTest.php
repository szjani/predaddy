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

use PHPUnit_Framework_TestCase;
use predaddy\commandhandling\SimpleCommand;
use predaddy\eventhandling\SimpleEvent;
use predaddy\MessageHandler;
use RuntimeException;
use trf4php\TransactionException;

/**
 * @package predaddy\util
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class TransactionalBusesTest extends PHPUnit_Framework_TestCase
{
    private $transactionManager;

    /**
     * @var TransactionalBuses
     */
    private $buses;

    /**
     * @var MessageHandler
     */
    private $eventHandler;

    protected function setUp()
    {
        $this->transactionManager = $this->getMock('\trf4php\TransactionManager');
        $this->buses = TransactionalBuses::create(
            $this->transactionManager,
            $this->getMock('\predaddy\domain\Repository')
        );
        $this->eventHandler = new MessageHandler();
        $this->buses->eventBus()->register($this->eventHandler);
    }

    /**
     * @test
     */
    public function collectedEventsMustBeSent()
    {
        $commandBus = $this->buses->commandBus();
        $event = new SimpleEvent();
        $commandBus->registerClosure(
            function (SimpleCommand $command) use ($event) {
                $this->buses->eventBus()->post($event);
            }
        );
        $this->transactionManager
            ->expects(self::once())
            ->method('commit');
        $commandBus->post(new SimpleCommand());
        self::assertTrue($this->eventHandler->called(1));
    }

    /**
     * @test
     */
    public function collectedEventsMustBeDeletedIfCommandHandlerFails()
    {
        $commandBus = $this->buses->commandBus();
        $event = new SimpleEvent();
        $commandBus->registerClosure(
            function (SimpleCommand $command) use ($event) {
                $this->buses->eventBus()->post($event);
                throw new RuntimeException('Expected exception');
            }
        );
        $this->transactionManager
            ->expects(self::never())
            ->method('commit');
        $this->transactionManager
            ->expects(self::once())
            ->method('rollback');

        $commandBus->post(new SimpleCommand());
        self::assertTrue($this->eventHandler->neverCalled());
    }

    /**
     * @test
     */
    public function collectedEventsMustBeDeletedIfCommitFails()
    {
        $commandBus = $this->buses->commandBus();
        $event = new SimpleEvent();
        $commandBus->registerClosure(
            function (SimpleCommand $command) use ($event) {
                $this->buses->eventBus()->post($event);
            }
        );
        $this->transactionManager
            ->expects(self::once())
            ->method('commit')
            ->will(self::throwException(new TransactionException()));
        try {
            $commandBus->post(new SimpleCommand());
            self::fail('Exception should be throws');
        } catch (TransactionException $e) {
        }
        self::assertTrue($this->eventHandler->neverCalled());
    }
}
