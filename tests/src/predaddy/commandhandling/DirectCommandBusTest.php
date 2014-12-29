<?php
/*
 * Copyright (c) 2012-2014 Janos Szurovecz
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
use precore\lang\IllegalStateException;
use precore\util\UUID;
use predaddy\domain\AbstractAggregateRoot;
use predaddy\domain\AggregateId;
use predaddy\domain\AggregateRoot;
use predaddy\fixture\article\ChangeText;
use predaddy\fixture\article\EventSourcedArticle;
use predaddy\inmemory\InMemoryRepository;
use predaddy\messagehandling\annotation\Subscribe;
use predaddy\messagehandling\DeadMessage;
use predaddy\messagehandling\interceptors\WrapInTransactionInterceptor;
use predaddy\messagehandling\util\MessageCallbackClosures;
use predaddy\messagehandling\util\SimpleMessageCallback;
use RuntimeException;
use trf4php\NOPTransactionManager;

class DirectCommandBusTest extends PHPUnit_Framework_TestCase
{
    private static $ANY_SIMPLE_COMMAND;

    private $transactionManager;
    private $repo;

    /**
     * @var DirectCommandBus
     */
    private $bus;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$ANY_SIMPLE_COMMAND = new SimpleCommand();
    }

    protected function setUp()
    {
        $this->transactionManager = new NOPTransactionManager();
        $this->repo = $this->getMock('\predaddy\domain\Repository');
        $trInterceptor = new WrapInTransactionInterceptor($this->transactionManager);
        $this->bus = DirectCommandBus::builder($this->repo)
            ->withExceptionHandler($trInterceptor)
            ->withInterceptors([$trInterceptor])
            ->build();
    }

    /**
     * @test
     */
    public function shouldNotBeForwarder()
    {
        $called = false;
        $this->bus->registerClosure(
            function (SimpleCommand $command) use (&$called) {
                $called = true;
            }
        );
        $this->bus->post(self::$ANY_SIMPLE_COMMAND);
        self::assertTrue($called);
    }

    /**
     * @test
     */
    public function shouldBeCalledIfNoExplicitHandler()
    {
        $this->repo
            ->expects(self::once())
            ->method('save');
        $this->bus->post(self::$ANY_SIMPLE_COMMAND);
    }

    /**
     * @test
     */
    public function shouldNotBeCalledIfExplicitHandler()
    {
        $this->repo
            ->expects(self::never())
            ->method('save');
        $this->bus->registerClosure(
            function (SimpleCommand $cmd) {
            }
        );
        $this->bus->post(self::$ANY_SIMPLE_COMMAND);
    }

    /**
     * @test
     */
    public function shouldNotBeCalledIfHandlerThrowsException()
    {
        $this->repo
            ->expects(self::never())
            ->method('save');
        $this->bus->registerClosure(
            function (SimpleCommand $cmd) {
                throw new RuntimeException('Expected exception');
            }
        );
        $this->bus->post(self::$ANY_SIMPLE_COMMAND);
    }

    /**
     * @test
     */
    public function shouldRaiseDeadMessageIfNoHandlerAndNotDirectCommand()
    {
        $command = $this->getMock('\predaddy\commandhandling\Command');
        $called = false;
        $this->bus->registerClosure(
            function (DeadMessage $message) use (&$called, $command) {
                self::assertSame($command, $message->wrappedMessage());
                $called = true;
            }
        );
        $this->bus->post($command);
        self::assertTrue($called);
    }

    /**
     * @test
     */
    public function exceptionMustBePassedToCallback()
    {
        $aggRoot = new TestAggregate01();
        $command = new ThrowException($aggRoot->getId()->value());
        $this->expectedToBeLoaded($aggRoot);

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
        $command = new ReturnResult($aggRoot->getId()->value());
        $this->expectedToBeLoaded($aggRoot);

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

    /**
     * @test
     */
    public function createNewAggregate()
    {
        $command = new CreateCommand();

        $this->repo
            ->expects(self::once())
            ->method('save')
            ->will(
                self::returnCallback(
                    function (TestAggregate $aggregate) {
                        self::assertTrue($aggregate->initialized());
                    }
                )
            );

        $this->bus->post($command);
    }

    private function expectedToBeLoaded(AggregateRoot $aggregateRoot)
    {
        $this->repo
            ->expects(self::once())
            ->method('load')
            ->will(self::returnValue($aggregateRoot));
    }

    /**
     * @test
     */
    public function loadAggregateIfIdNotNull()
    {
        $aggregate = new TestAggregate(new CreateCommand());
        $aggregateId = UUID::randomUUID()->toString();
        $command = new CalledCommand($aggregateId);

        $this->repo
            ->expects(self::once())
            ->method('load')
            ->will(
                self::returnCallback(
                    function (AggregateId $aggregateIdObj) use ($aggregateId, $aggregate) {
                        self::assertEquals($aggregateId, $aggregateIdObj->value());
                        return $aggregate;
                    }
                )
            );

        $this->repo
            ->expects(self::once())
            ->method('save')
            ->will(
                self::returnCallback(
                    function (TestAggregate $aggregate) {
                        self::assertTrue($aggregate->called());
                    }
                )
            );

        $this->bus->post($command);
    }

    /**
     * @test
     */
    public function shouldFailStateHashCheck()
    {
        $repository = new InMemoryRepository();
        $article = new EventSourcedArticle('author', 'text');
        $repository->save($article);

        $bus = DirectCommandBus::builder($repository)->build();

        $command = new ChangeText($article->getId()->value(), 'invalid', 'newText');
        $callback = new SimpleMessageCallback();
        $bus->post($command, $callback);
        self::assertTrue($callback->getException() instanceof IllegalStateException);
    }
}

class TestAggregate extends AbstractAggregateRoot
{
    private $initialized = false;
    private $called = false;

    /**
     * @Subscribe
     * @param CreateCommand $command
     */
    public function __construct(CreateCommand $command)
    {
        $this->initialized = true;
    }

    /**
     * @return AggregateId
     */
    public function getId()
    {
        // TODO: Implement getId() method.
    }

    public function called()
    {
        return $this->called;
    }

    public function initialized()
    {
        return $this->initialized;
    }

    /**
     * @Subscribe
     * @param CalledCommand $command
     */
    public function handler(CalledCommand $command)
    {
        $this->called = true;
    }
}

class CreateCommand extends AbstractCommand implements DirectCommand
{

    /**
     * @return string
     */
    public function aggregateClass()
    {
        return TestAggregate::className();
    }
}

class CalledCommand extends AbstractCommand implements DirectCommand
{

    /**
     * @return string
     */
    public function aggregateClass()
    {
        return TestAggregate::className();
    }
}
