<?php
declare(strict_types=1);

namespace predaddy\commandhandling;

use Exception;
use PHPUnit\Framework\TestCase;
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

class DirectCommandBusTest extends TestCase
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
        $this->repo = $this->getMockBuilder('\predaddy\domain\Repository')->getMock();
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
        $command = $this->getMockBuilder('\predaddy\commandhandling\Command')->getMock();
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
    public function getId() : AggregateId
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
    public function aggregateClass() : string
    {
        return TestAggregate::className();
    }
}

class CalledCommand extends AbstractCommand implements DirectCommand
{

    /**
     * @return string
     */
    public function aggregateClass() : string
    {
        return TestAggregate::className();
    }
}
