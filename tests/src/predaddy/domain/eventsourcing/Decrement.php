<?php
declare(strict_types=1);

namespace predaddy\domain\eventsourcing;

use predaddy\commandhandling\AbstractCommand;
use predaddy\commandhandling\DirectCommand;

/**
 * @package predaddy\domain
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class Decrement extends AbstractCommand implements DirectCommand
{
    /**
     * @return string
     */
    public function aggregateClass() : string
    {
        return EventSourcedUser::className();
    }
}
