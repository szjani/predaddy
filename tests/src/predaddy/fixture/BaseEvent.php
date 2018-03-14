<?php
declare(strict_types=1);

namespace predaddy\fixture;

use predaddy\domain\AbstractDomainEvent;
use predaddy\domain\AggregateId;

/**
 * @package predaddy
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class BaseEvent extends AbstractDomainEvent
{
    public function setStateHash($stateHash)
    {
        $this->stateHash = $stateHash;
    }
}
