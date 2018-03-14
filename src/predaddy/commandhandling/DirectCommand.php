<?php
declare(strict_types=1);

namespace predaddy\commandhandling;

/**
 * Extends Command interface and provides the class name
 * of the aggregate root which can handle this command.
 *
 * Useful when loading and persisting the aggregate type can be generalized.
 * Should be used with DirectCommandBus.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface DirectCommand extends Command
{
    /**
     * @return string
     */
    public function aggregateClass() : string;
}
