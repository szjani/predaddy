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

namespace predaddy\util\test;

use BadMethodCallException;
use Closure;
use Exception;
use PHPUnit_Framework_TestCase;
use precore\lang\ObjectClass;
use predaddy\commandhandling\AbstractCommand;
use predaddy\commandhandling\Command;
use predaddy\commandhandling\CommandBus;
use predaddy\commandhandling\DirectCommandBus;
use predaddy\domain\AbstractDomainEvent;
use predaddy\domain\AggregateId;
use predaddy\domain\GenericAggregateId;
use predaddy\domain\DomainEvent;
use predaddy\domain\EventPublisher;
use predaddy\domain\Repository;
use predaddy\eventhandling\EventBus;
use predaddy\messagehandling\annotation\Subscribe;
use predaddy\messagehandling\MessageCallback;

/**
 * Class Fixture
 *
 * @package predaddy\util\test
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class Fixture implements MessageCallback
{
    const EVENT_NUMBER_MISMATCH = 'Raised and expected number of events mismatches';

    /**
     * @var DomainEvent[]
     */
    private $then = [];

    /**
     * @var DomainEvent[]
     */
    private $raisedEvents = [];

    /**
     * @var Command
     */
    private $command;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @var string
     */
    private $aggregateClass;

    /**
     * @var mixed
     */
    private $expectedReturnValue;

    /**
     * @var mixed
     */
    private $commandResult;

    /**
     * @var bool
     */
    private $checkReturnValue = false;

    /**
     * @var null|string
     */
    private $expectedExceptionClass = null;

    /**
     * @var null|string
     */
    private $expectedExceptionMessage = null;

    /**
     * @var null|Exception
     */
    private $raisedException;

    /**
     * @var null|Repository
     */
    private $repository;

    /**
     * @var GenericAggregateId|null
     */
    private $aggregateId;

    public function __construct($aggregateClass, Repository $repository = null)
    {
        $this->aggregateClass = $aggregateClass;
        $this->commandBus = $repository === null
            ? new CommandBus()
            : DirectCommandBus::builder($repository)->build();
        $this->eventBus = new EventBus();
        EventPublisher::instance()->setEventBus($this->eventBus);
        $this->repository = $repository;
        //$this->aggregateId = new GenericAggregateId(UUID::randomUUID()->toString(), $this->getAggregateClass());
    }

    /**
     * These commands will be initialize the AR. The raised domain events will be ignored.
     * It works only with non-ES aggregates.
     *
     * @param Command $commands one ore more commands
     * @throws BadMethodCallException
     * @return CommandSourcedFixture
     */
    public function givenCommands(Command $commands)
    {
        throw new BadMethodCallException("This method is not supported");
    }

    /**
     * These events will be initialize the AR. It works only with ES aggregates.
     *
     * @param DomainEvent $events
     * @throws BadMethodCallException
     * @return EventSourcedFixture
     */
    public function givenEvents(DomainEvent $events)
    {
        throw new BadMethodCallException("This method is not supported");
    }

    /**
     * Returns the registered or dynamically generated repository.
     *
     * @return null|Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * These events are expected to be raised. Starts the evaluation.
     *
     * @param DomainEvent $events one or more events
     */
    public function expectEvents(DomainEvent $events)
    {
        $this->then = func_get_args();
        $this->evaluate();
    }

    /**
     * No expected events. Starts the evaluation.
     */
    public function expectNoEvents()
    {
        $this->evaluate();
    }

    /**
     * The AR's behavior will be tested when this command is applied.
     *
     * @param Command $command
     * @return $this
     */
    public function when(Command $command)
    {
        if ($this->aggregateId !== null && $command instanceof AbstractCommand) {
            CommandPopulator::populate($this->getAggregateId()->value(), $command);
        }
        $this->command = $command;
        return $this;
    }

    /**
     * The given value must be returned by the command handler.
     *
     * @param mixed $value
     * @return $this
     */
    public function expectReturnValue($value)
    {
        $this->checkReturnValue = true;
        $this->expectedReturnValue = $value;
        return $this;
    }

    /**
     * The given type of exception must be be thrown when the command is applied. Starts the evaluation.
     *
     * @param string $class the type of the exception
     * @param null|string $message the exception message
     */
    public function expectException($class, $message = null)
    {
        $this->expectedExceptionClass = $class;
        $this->expectedExceptionMessage = $message;
        $this->evaluate();
    }

    /**
     * The command handler which can handle the tested command.
     * If the command implements {@link DirectCommand} and is processed directly
     * by the AR, does not need to be used.
     *
     * @param $handler
     * @return $this
     */
    public function registerAnnotatedCommandHandler($handler)
    {
        $this->commandBus->register($handler);
        return $this;
    }

    /**
     * The same as {@link registerAnnotatedCommandHandler()} but with closure.
     *
     * @param callable $closure
     * @return $this
     */
    public function registerCommandClosure(Closure $closure)
    {
        $this->commandBus->registerClosure($closure);
        return $this;
    }

    /**
     * The aggregate ID generated by the AR, or defined somehow else.
     * Can be obtained in order to do further tests if required.
     *
     * @return AggregateId
     */
    public function getAggregateId()
    {
        return $this->aggregateId;
    }

    /**
     * @param mixed $result
     * @return void
     */
    public function onSuccess($result)
    {
        $this->commandResult = $result;
    }

    /**
     * @param Exception $exception
     * @return void
     */
    public function onFailure(Exception $exception)
    {
        $this->raisedException = $exception;
    }

    /**
     * @Subscribe
     * @param DomainEvent $event
     */
    public function catchEvent(DomainEvent $event)
    {
        $this->raisedEvents[] = $event;
        $this->setAggregateId($event->aggregateId());
    }

    /**
     * @param AggregateId $aggregateId
     */
    protected function setAggregateId(AggregateId $aggregateId)
    {
        $this->aggregateId = $aggregateId;
    }

    /**
     * @return CommandBus
     */
    protected function getCommandBus()
    {
        return $this->commandBus;
    }

    /**
     * @return EventBus
     */
    protected function getEventBus()
    {
        return $this->eventBus;
    }

    /**
     * @return ObjectClass
     */
    protected function getAggregateClass()
    {
        return $this->aggregateClass;
    }

    private function evaluate()
    {
        $this->eventBus->register($this);
        $this->commandBus->post($this->command, $this);
        $this->checkReturnValue();
        $this->checkThrownException();
        $this->checkRaisedEvents();
    }

    private function checkReturnValue()
    {
        if ($this->checkReturnValue) {
            PHPUnit_Framework_TestCase::assertEquals($this->expectedReturnValue, $this->commandResult);
        }
    }

    private function checkThrownException()
    {
        if ($this->raisedException !== null) {
            if ($this->expectedExceptionClass === null) {
                throw $this->raisedException;
            }
            PHPUnit_Framework_TestCase::assertInstanceOf($this->expectedExceptionClass, $this->raisedException);
            PHPUnit_Framework_TestCase::assertEquals(
                $this->expectedExceptionMessage,
                $this->raisedException->getMessage()
            );
        }
    }

    private function checkRaisedEvents()
    {
        $thenCount = count($this->then);
        $raisedCount = count($this->raisedEvents);
        PHPUnit_Framework_TestCase::assertEquals($thenCount, $raisedCount, self::EVENT_NUMBER_MISMATCH);
        for ($i = 0; $i < $thenCount; $i++) {
            $expectedEvent = $this->then[$i];
            $raisedEvent = $this->raisedEvents[$i];
            if ($raisedEvent instanceof AbstractDomainEvent) {
                $raisedEvent = DomainEventMetaReset::reset($raisedEvent);
            }
            if ($expectedEvent instanceof AbstractDomainEvent) {
                $expectedEvent = DomainEventMetaReset::reset($expectedEvent);
            }
            PHPUnit_Framework_TestCase::assertEquals($expectedEvent, $raisedEvent);
        }
    }
}

class DomainEventMetaReset extends AbstractDomainEvent
{
    public static function reset(AbstractDomainEvent $event)
    {
        $event->id = '';
        $event->aggregateClass = '';
        $event->aggregateValue = '';
        $event->stateHash = null;
        $event->created = null;
        return $event;
    }
}

class CommandPopulator extends AbstractCommand
{
    public static function populate($aggregateId, AbstractCommand $command)
    {
        $command->aggregateId = $aggregateId;
    }
}
