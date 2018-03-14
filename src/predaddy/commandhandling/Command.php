<?php
declare(strict_types=1);

namespace predaddy\commandhandling;

use predaddy\messagehandling\Message;

/**
 * Base interface for all commands in the application.
 * All classes that represent a command should implement this interface.
 *
 * The aggregate id is intended to store the identifier of the aggregate
 * which need to be loaded from a persistent store. If aggregate id is null,
 * a new aggregate instance need to be created.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface Command extends Message
{
    /**
     * @return string|null null if it is a create command
     */
    public function aggregateId() : ?string;
}
