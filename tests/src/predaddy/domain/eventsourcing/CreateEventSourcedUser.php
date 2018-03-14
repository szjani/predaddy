<?php
declare(strict_types=1);

namespace predaddy\domain\eventsourcing;

use predaddy\commandhandling\AbstractCommand;
use predaddy\commandhandling\DirectCommand;

class CreateEventSourcedUser extends AbstractCommand implements DirectCommand
{
    /**
     * @return string
     */
    public function aggregateClass() : string
    {
        return EventSourcedUser::className();
    }
}
