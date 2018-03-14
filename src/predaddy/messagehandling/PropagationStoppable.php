<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

/**
 * A message can implement this interface to be able to notify the message bus,
 * that the rest of the message handlers should not be called.
 *
 * Note, that it is not required to be considered by the message bus.
 *
 * @package predaddy\messagehandling
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface PropagationStoppable
{
    /**
     * The rest of the message handlers should not be called by the message bus.
     *
     * @return void
     */
    public function stopPropagation() : void;

    /**
     * Whether the propagation is marked as stopped.
     *
     * @return boolean
     */
    public function isPropagationStopped() : bool;
}
