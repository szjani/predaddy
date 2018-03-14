<?php
declare(strict_types=1);

namespace predaddy\domain;

class UserCreated extends AbstractDomainEvent
{
    /**
     * @return AggregateId
     */
    public function getUserId()
    {
        return $this->aggregateId();
    }
}
