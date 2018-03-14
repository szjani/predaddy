<?php
declare(strict_types=1);

namespace predaddy\commandhandling;

/**
 * @package predaddy\commandhandling
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ReturnResult extends AbstractCommand implements DirectCommand
{
    /**
     * @return string
     */
    public function aggregateClass() : string
    {
        return TestAggregate01::className();
    }
}
