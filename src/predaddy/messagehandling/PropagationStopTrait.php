<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

/**
 * Can be used for {@link PropagationStoppable} implementations.
 *
 * @package predaddy\messagehandling
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
trait PropagationStopTrait
{
    private $stopped = false;

    /**
     * @return void
     */
    public function stopPropagation() : void
    {
        $this->stopped = true;
    }

    /**
     * @return boolean
     */
    public function isPropagationStopped() : bool
    {
        return $this->stopped;
    }
}
