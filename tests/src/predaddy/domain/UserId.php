<?php
declare(strict_types=1);

namespace predaddy\domain;

/**
 * @package predaddy\domain
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class UserId extends UUIDAggregateId
{
    /**
     * @return string FQCN
     */
    public function aggregateClass() : string
    {
        return User::className();
    }
}
