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

use Exception;
use PHPUnit_Framework_TestCase;
use precore\lang\Object;
use precore\lang\ObjectClass;
use predaddy\domain\EventSourcedUser;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\interceptors\WrapInTransactionInterceptor;
use predaddy\messagehandling\SimpleMessageBusFactory;
use predaddy\messagehandling\util\MessageCallbackClosures;
use RuntimeException;
use trf4php\NOPTransactionManager;

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
        $this->messageBusFactory = new SimpleMessageBusFactory($this->handlerDescFact);
        $trInterceptor = new WrapInTransactionInterceptor($this->transactionManager);
        $this->bus = new DirectCommandBus(
            $this->repoRepo,
            $this->messageBusFactory,
            $this->handlerDescFact,
            [$trInterceptor],
            $trInterceptor
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
        $this->repoRepo
            ->expects(self::never())
            ->method('getRepository');
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
        $this->repoRepo
            ->expects(self::never())
            ->method('getRepository');
        $this->bus->registerClosure(
            function (SimpleCommand $cmd) {
                throw new RuntimeException('Expected exception');
            }
        );
        $this->bus->post(new SimpleCommand());
    }

    /**
     * @test
     */
    public function exceptionMustBePassedToCallback()
    {
        $aggRoot = new TestAggregate01();
        $command = new ThrowException($aggRoot->getId()->getValue());
        $repo = $this->getMock('\predaddy\domain\Repository');
        $repo
            ->expects(self::once())
            ->method('load')
            ->will(self::returnValue($aggRoot));
        $this->repoRepo
            ->expects(self::once())
            ->method('getRepository')
            ->with(ObjectClass::forName($command->getAggregateClass()))
            ->will(self::returnValue($repo));

        $exceptionThrown = false;
        $callback = MessageCallbackClosures::builder()
            ->failureClosure(
                function (Exception $exp) use (&$exceptionThrown) {
                    $exceptionThrown = true;
                }
            )
            ->build();
        $this->bus->post($command, $callback);
        self::assertTrue($exceptionThrown);
    }

    /**
     * @test
     */
    public function resultMustBePassedToCallback()
    {
        $aggRoot = new TestAggregate01();
        $command = new ReturnResult($aggRoot->getId()->getValue());
        $repo = $this->getMock('\predaddy\domain\Repository');
        $repo
            ->expects(self::once())
            ->method('load')
            ->will(self::returnValue($aggRoot));
        $this->repoRepo
            ->expects(self::once())
            ->method('getRepository')
            ->with(ObjectClass::forName($command->getAggregateClass()))
            ->will(self::returnValue($repo));

        $resultPassed = false;
        $callback = MessageCallbackClosures::builder()
            ->successClosure(
                function ($result) use (&$resultPassed) {
                    $resultPassed = $result === TestAggregate01::RESULT;
                }
            )
            ->build();
        $this->bus->post($command, $callback);
        self::assertTrue($resultPassed);
    }
}
