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
use predaddy\domain\EventSourcedUser;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use RuntimeException;
use trf4php\NOPTransactionManager;

require_once 'SimpleCommand.php';
require_once __DIR__ . '/../domain/EventSourcedUser.php';

class DirectCommandBusTest extends PHPUnit_Framework_TestCase
{
    private $handlerDescFact;
    private $transactionManager;
    private $repoRepo;
    private $messageBusFactory;

    /**
     * @var DirectCommandBus
     */
    private $bus;

    protected function setUp()
    {
        $this->handlerDescFact = new AnnotatedMessageHandlerDescriptorFactory(new CommandFunctionDescriptorFactory());
        $this->transactionManager = new NOPTransactionManager();
        $this->repoRepo = $this->getMock('\predaddy\domain\RepositoryRepository');
        $this->messageBusFactory = $this->getMock('\predaddy\messagehandling\MessageBusFactory');
        $this->bus = new DirectCommandBus(
            $this->handlerDescFact,
            $this->transactionManager,
            $this->repoRepo,
            $this->messageBusFactory
        );
    }

    /**
     * @test
     */
    public function shouldNotBeForwarder()
    {
        $this->repoRepo
            ->expects(self::never())
            ->method('getRepository');

        $called = false;
        $this->bus->registerClosure(
            function (SimpleCommand $command) use (&$called) {
                $called = true;
            }
        );

        $this->bus->post(new SimpleCommand());
        self::assertTrue($called);
    }

    /**
     * @test
     */
    public function shouldBeCalledIfNoExplicitHandler()
    {
        $innerBus = $this->getMock('\predaddy\messagehandling\MessageBus');
        $this->messageBusFactory
            ->expects(self::once())
            ->method('createBus')
            ->with(EventSourcedUser::className())
            ->will(self::returnValue($innerBus));
        $repo = $this->getMock('\predaddy\domain\Repository');
        $this->repoRepo
            ->expects(self::once())
            ->method('getRepository')
            ->will(self::returnValue($repo));
        $this->bus->post(new SimpleCommand());
    }

    /**
     * @test
     */
    public function shouldNotBeCalledIfExplicitHandler()
    {
        $this->messageBusFactory
            ->expects(self::never())
            ->method('createBus');
        $this->bus->registerClosure(
            function (SimpleCommand $cmd) {
            }
        );
        $this->bus->post(new SimpleCommand());
    }

    /**
     * @test
     */
    public function shouldNotBeCalledIfHandlerThrowsException()
    {
        $this->messageBusFactory
            ->expects(self::never())
            ->method('createBus');
        $this->bus->registerClosure(
            function (SimpleCommand $cmd) {
                throw new RuntimeException('Expected exception');
            }
        );
        $this->bus->post(new SimpleCommand());
    }
}
