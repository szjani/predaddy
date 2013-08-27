<?php
/*
 * Copyright © 2013 CME Hungary ZRT.
 */

namespace predaddy\domain;

class UserCreated extends DomainEvent
{
    private $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
