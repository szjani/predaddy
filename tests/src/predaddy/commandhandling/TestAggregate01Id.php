<?php
declare(strict_types=1);

namespace predaddy\commandhandling;

use predaddy\domain\UUIDAggregateId;

/**
 * @package predaddy\commandhandling
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class TestAggregate01Id extends UUIDAggregateId
{
    /**
     * @return string FQCN
     */
    public function aggregateClass() : string
    {
        return TestAggregate01::className();
    }
}
