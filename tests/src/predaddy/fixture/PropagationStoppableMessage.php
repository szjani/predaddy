<?php
declare(strict_types=1);

namespace predaddy\fixture;

use predaddy\messagehandling\PropagationStoppable;
use predaddy\messagehandling\PropagationStopTrait;

/**
 * Class PropagationStoppableMessage
 *
 * @package predaddy\fixture
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class PropagationStoppableMessage implements PropagationStoppable
{
    use PropagationStopTrait;
}
