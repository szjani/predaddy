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

namespace predaddy\commandhandling;

use PHPUnit_Framework_TestCase;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use trf4php\NOPTransactionManager;

require_once 'SimpleCommand.php';

class DirectCommandBusTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldNotBeForwarder()
    {
        $handlerDescFact = new AnnotatedMessageHandlerDescriptorFactory(new CommandFunctionDescriptorFactory());
        $transactionManager = new NOPTransactionManager();
        $repoRepo = $this->getMock('\predaddy\domain\RepositoryRepository');
        $messageBusFactory = $this->getMock('\predaddy\messagehandling\MessageBusFactory');
        $bus = new DirectCommandBus($handlerDescFact, $transactionManager, $repoRepo, $messageBusFactory);

        $repoRepo
            ->expects(self::never())
            ->method('getRepository');

        $called = false;
        $bus->registerClosure(
            function (SimpleCommand $command) use (&$called) {
                $called = true;
            }
        );

        $bus->post(new SimpleCommand());
        self::assertTrue($called);
    }
}
