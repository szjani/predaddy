<?php
declare(strict_types=1);

namespace predaddy\domain\eventsourcing;

use predaddy\domain\UUIDAggregateId;

/**
 * @package predaddy\domain
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class EventSourcedUserId extends UUIDAggregateId
{
    /**
     * @return string FQCN
     */
    public function aggregateClass() : string
    {
        return EventSourcedUser::className();
    }
}
