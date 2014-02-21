<?php
/*
 * Copyright (c) 2013 Szurovecz János
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

namespace predaddy\messagehandling\command;

use ArrayIterator;
use PHPUnit_Framework_TestCase;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\interceptors\WrapInTransactionInterceptor;
use RuntimeException;

require_once 'SimpleCommand.php';

class CommandBusTest extends PHPUnit_Framework_TestCase
{
    private $tm;

    private $functionDescriptorFactory;

    private $handlerDescriptorFactory;

    /**
     * @var CommandBus
     */
    private $commandBus;

    public function setUp()
    {
        $this->tm = $this->getMock('trf4php\TransactionManager');
        $this->functionDescriptorFactory = new CommandFunctionDescriptorFactory();
        $this->handlerDescriptorFactory = new AnnotatedMessageHandlerDescriptorFactory($this->functionDescriptorFactory);
        $this->commandBus = new CommandBus(
            $this->handlerDescriptorFactory,
            $this->tm
        );
    }

    public function testNoHandler()
    {
        $this->tm
            ->expects(self::never())
            ->method('beginTransaction');
        $this->tm
            ->expects(self::never())
            ->method('commit');
        $this->tm
            ->expects(self::never())
            ->method('rollback');

        $this->commandBus->post(new SimpleCommand());
    }

    public function testTransactionWrapping()
    {
        $called = false;
        $this->commandBus->registerClosure(
            function (SimpleCommand $command) use (&$called) {
                $called = true;
                CommandBusTest::assertEquals($command->getMessageIdentifier(), $command->getCommandIdentifier());
            }
        );
        $this->tm
            ->expects(self::once())
            ->method('beginTransaction');
        $this->tm
            ->expects(self::once())
            ->method('commit');
        $this->tm
            ->expects(self::never())
            ->method('rollback');

        $this->commandBus->post(new SimpleCommand());
        self::assertTrue($called);
    }

    public function testRollback()
    {
        $called = false;
        $this->commandBus->registerClosure(
            function (SimpleCommand $command) use (&$called) {
                $called = true;
                throw new RuntimeException("Oops");
            }
        );
        $this->tm
            ->expects(self::once())
            ->method('beginTransaction');
        $this->tm
            ->expects(self::never())
            ->method('commit');
        $this->tm
            ->expects(self::once())
            ->method('rollback');

        $this->commandBus->post(new SimpleCommand());
        self::assertTrue($called);
    }

    public function testSetInterceptor()
    {
        $called = false;
        $this->commandBus->registerClosure(
            function (SimpleCommand $command) use (&$called) {
                $called = true;
            }
        );
        $interceptor = $this->getMock('\predaddy\messagehandling\HandlerInterceptor');
        $interceptor
            ->expects(self::once())
            ->method('invoke')
            ->will(self::throwException(new RuntimeException("Ooops")));
        $this->commandBus->setInterceptors(
            new ArrayIterator(
                array(
                    new WrapInTransactionInterceptor($this->tm),
                    $interceptor
                )
            )
        );

        $this->tm
            ->expects(self::once())
            ->method('beginTransaction');
        $this->tm
            ->expects(self::never())
            ->method('commit');
        $this->tm
            ->expects(self::once())
            ->method('rollback');

        $this->commandBus->post(new SimpleCommand());
        self::assertFalse($called);
    }

    public function testExactCommandType()
    {
        $called = false;
        $this->commandBus->registerClosure(
            function (Command $command) use (&$called) {
                $called = true;
            }
        );
        $this->tm
            ->expects(self::never())
            ->method('beginTransaction');
        $this->tm
            ->expects(self::never())
            ->method('commit');
        $this->tm
            ->expects(self::never())
            ->method('rollback');

        $this->commandBus->post(new SimpleCommand());
        self::assertFalse($called);
    }
}
