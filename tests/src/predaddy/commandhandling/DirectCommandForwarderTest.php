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
use predaddy\domain\AggregateRoot;
use predaddy\domain\AbstractAggregateRoot;
use predaddy\domain\DomainTestCase;
use predaddy\domain\impl\InMemoryRepository;
use predaddy\fixture\article\ArticleCreated;
use predaddy\fixture\article\EventSourcedArticle;
use predaddy\messagehandling\DeadMessage;
use predaddy\fixture\ChangeText;

class DirectCommandForwarderTest extends DomainTestCase
{
    private $repository;
    private $messageBusFactory;

    /**
     * @var DirectCommandForwarder
     */
    private $directCommandForwarder;

    protected function setUp()
    {
        parent::setUp();
        $this->messageBusFactory = $this->getMock('\predaddy\messagehandling\MessageBusFactory');
        $this->repository = $this->getMock('\predaddy\domain\Repository');
        $this->directCommandForwarder = new DirectCommandForwarder(
            $this->repository,
            $this->messageBusFactory
        );
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
        $aggregateClass = __NAMESPACE__ . '\TestAggregate';
        $command = $this->getMock(__NAMESPACE__ . '\DirectCommand');
        $command
            ->expects(self::once())
            ->method('aggregateClass')
            ->will(self::returnValue($aggregateClass));
        $command
            ->expects(self::once())
            ->method('aggregateId')
            ->will(self::returnValue(null));

        $this->repository
            ->expects(self::once())
            ->method('save')
            ->will(
                self::returnCallback(
                    function (AggregateRoot $aggregate) use ($aggregateClass) {
                        DirectCommandForwarderTest::assertInstanceOf($aggregateClass, $aggregate);
                    }
                )
            );

        $bus = $this->getMock('\predaddy\messagehandling\MessageBus');
        $this->messageBusFactory
            ->expects(self::once())
            ->method('createBus')
            ->with($aggregateClass)
            ->will(self::returnValue($bus));

        $bus
            ->expects(self::once())
            ->method('register');
        $bus
            ->expects(self::once())
            ->method('post')
            ->with($command);

        $this->directCommandForwarder->catchDeadCommand(new DeadMessage($command));
    }

    /**
     * @test
     */
    public function loadAggregateIfIdNotNull()
    {
        $aggregate = $this->getMock('\predaddy\domain\AggregateRoot');
        $aggregateClass = __NAMESPACE__ . '\TestAggregate';
        $aggregateId = UUID::randomUUID()->toString();
        $command = $this->getMock(__NAMESPACE__ . '\DirectCommand');
        $command
            ->expects(self::atLeastOnce())
            ->method('aggregateClass')
            ->will(self::returnValue($aggregateClass));
        $command
            ->expects(self::once())
            ->method('aggregateId')
            ->will(self::returnValue($aggregateId));

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

        $bus = $this->getMock('\predaddy\messagehandling\MessageBus');
        $this->messageBusFactory
            ->expects(self::once())
            ->method('createBus')
            ->with($aggregateClass)
            ->will(self::returnValue($bus));
        $bus
            ->expects(self::once())
            ->method('register')
            ->with($aggregate);

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

        $commandForwarder = new DirectCommandForwarder(
            $repository,
            $this->messageBusFactory
        );

        $command = new ChangeText($article->getId()->value(), 'invalid', 'newText');
        $commandForwarder->catchDeadCommand(new DeadMessage($command));
    }
}

class TestAggregate extends AbstractAggregateRoot
{
    /**
     * @return AggregateId
     */
    public function getId()
    {
        // TODO: Implement getId() method.
    }
}
