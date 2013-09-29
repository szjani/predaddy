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
use RuntimeException;

require_once 'SimpleCommand.php';

class AnnotationBasedCommandBusTest extends PHPUnit_Framework_TestCase
{
    private $tm;

    public function setUp()
    {
        $this->tm = $this->getMock('\trf4php\TransactionManager');
    }

    public function testNoHandler()
    {
        $commandBus = new AnnotationBasedCommandBus(__METHOD__, $this->tm);

        $this->tm
            ->expects(self::never())
            ->method('beginTransaction');
        $this->tm
            ->expects(self::never())
            ->method('commit');
        $this->tm
            ->expects(self::never())
            ->method('rollback');

        $commandBus->post(new SimpleCommand());
    }

    public function testTransactionWrapping()
    {
        $commandBus = new AnnotationBasedCommandBus(__METHOD__, $this->tm);
        $called = false;
        $commandBus->registerClosure(
            function (SimpleCommand $command) use (&$called) {
                $called = true;
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

        $commandBus->post(new SimpleCommand());
        self::assertTrue($called);
    }

    public function testRollback()
    {
        $commandBus = new AnnotationBasedCommandBus(__METHOD__, $this->tm);
        $called = false;
        $commandBus->registerClosure(
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

        $commandBus->post(new SimpleCommand());
        self::assertTrue($called);
    }

    public function testSetInterceptor()
    {
        $commandBus = new AnnotationBasedCommandBus(__METHOD__, $this->tm);
        $called = false;
        $commandBus->registerClosure(
            function (SimpleCommand $command) use (&$called) {
                $called = true;
            }
        );
        $interceptor = $this->getMock('\predaddy\messagehandling\HandlerInterceptor');
        $interceptor
            ->expects(self::once())
            ->method('invoke')
            ->will(self::throwException(new RuntimeException("Ooops")));
        $commandBus->setInterceptors(new ArrayIterator(array($interceptor)));

        $this->tm
            ->expects(self::once())
            ->method('beginTransaction');
        $this->tm
            ->expects(self::never())
            ->method('commit');
        $this->tm
            ->expects(self::once())
            ->method('rollback');

        $commandBus->post(new SimpleCommand());
        self::assertFalse($called);
    }

    public function testExactCommandType()
    {
        $commandBus = new AnnotationBasedCommandBus(__METHOD__, $this->tm);
        $called = false;
        $commandBus->registerClosure(
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

        $commandBus->post(new SimpleCommand());
        self::assertFalse($called);
    }
}
