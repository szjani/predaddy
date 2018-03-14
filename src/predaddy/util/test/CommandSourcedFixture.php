<?php
declare(strict_types=1);

namespace predaddy\util\test;

use predaddy\commandhandling\Command;
use predaddy\domain\DomainEvent;

/**
 * Class CommandSourcedFixture
 *
 * @package predaddy\util\test
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class CommandSourcedFixture extends Fixture
{
    /**
     * @var array
     */
    private $givenCommands = [];

    /**
     * These commands will be initialize the AR. The raised domain events will be ignored.
     * It works only with non-ES aggregates.
     *
     * @param Command $commands one ore more commands
     * @return CommandSourcedFixture
     */
    public function givenCommands(Command $commands)
    {
        $this->givenCommands = func_get_args();
        $eventHandler = function (DomainEvent $event) {
            if ($this->getAggregateId() === null) {
                $this->setAggregateId($event->aggregateId());
                foreach ($this->givenCommands as $command) {
                    CommandPopulator::populate($this->getAggregateId()->value(), $command);
                }
            }
        };
        $this->getEventBus()->registerClosure($eventHandler);
        foreach ($this->givenCommands as $command) {
            $this->getCommandBus()->post($command);
        }
        $this->getEventBus()->unregisterClosure($eventHandler);
        return $this;
    }
}
