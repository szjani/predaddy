<?php
declare(strict_types=1);

namespace predaddy\util;

use predaddy\commandhandling\CommandBus;
use predaddy\eventhandling\EventBus;

/**
 * Utility class which simplifies the EventBus and CommandBus creation process.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class TransactionalBuses
{
    private $commandBus;
    private $eventBus;

    /**
     * @param CommandBus $commandBus
     * @param EventBus $eventBus
     */
    public function __construct(CommandBus $commandBus, EventBus $eventBus)
    {
        $this->commandBus = $commandBus;
        $this->eventBus = $eventBus;
    }

    /**
     * @return CommandBus
     */
    public function commandBus() : CommandBus
    {
        return $this->commandBus;
    }

    /**
     * @return EventBus
     */
    public function eventBus() : EventBus
    {
        return $this->eventBus;
    }
}
