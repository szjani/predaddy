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

use precore\util\UUID;
use predaddy\domain\AggregateId;
use predaddy\domain\AbstractAggregateRoot;
use predaddy\domain\DomainTestCase;
use predaddy\fixture\article\ChangeText;
use predaddy\fixture\article\EventSourcedArticle;
use predaddy\inmemory\InMemoryRepository;
use predaddy\messagehandling\annotation\Subscribe;
use predaddy\messagehandling\DeadMessage;

class DirectCommandForwarderTest extends DomainTestCase
{
    private static $ANY_DIRECT_COMMAND;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var DirectCommandForwarder
     */
    private $directCommandForwarder;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = $this->getMock('\predaddy\domain\Repository');
        $this->directCommandForwarder = new DirectCommandForwarder($this->repository);
        self::$ANY_DIRECT_COMMAND = $this->getMock(__NAMESPACE__ . '\DirectCommand');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function shouldFailWithNotDirectCommand()
    {
        $command = $this->getMock(__NAMESPACE__ . '\Command');
        $deadMessage = new DeadMessage($command);
        $this->directCommandForwarder->catchDeadCommand($deadMessage);
    }

    /**
     * @test
     */
    public function createNewAggregate()
    {
        $command = new CreateCommand();

        $this->repository
            ->expects(self::once())
            ->method('save')
            ->will(
                self::returnCallback(
                    function (TestAggregate $aggregate) {
                        self::assertTrue($aggregate->initialized());
                    }
                )
            );

        $this->directCommandForwarder->catchDeadCommand(new DeadMessage($command));
    }

    /**
     * @test
     */
    public function loadAggregateIfIdNotNull()
    {
        $aggregate = new TestAggregate(new CreateCommand());
        $aggregateId = UUID::randomUUID()->toString();
        $command = new CalledCommand($aggregateId);

        $this->repository
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

        $this->repository
            ->expects(self::once())
            ->method('save')
            ->will(
                self::returnCallback(
                    function (TestAggregate $aggregate) {
                        self::assertTrue($aggregate->called());
                    }
                )
            );

        $this->directCommandForwarder->catchDeadCommand(new DeadMessage($command));
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    public function shouldFailStateHashCheck()
    {
        $repository = new InMemoryRepository();
        $article = new EventSourcedArticle('author', 'text');
        $repository->save($article);

        $commandForwarder = new DirectCommandForwarder($repository);

        $command = new ChangeText($article->getId()->value(), 'invalid', 'newText');
        $commandForwarder->catchDeadCommand(new DeadMessage($command));
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

class CreateCommand extends AbstractDirectCommand
{

    /**
     * @return string
     */
    public function aggregateClass()
    {
        return TestAggregate::className();
    }
}

class CalledCommand extends AbstractDirectCommand
{

    /**
     * @return string
     */
    public function aggregateClass()
    {
        return TestAggregate::className();
    }
}
