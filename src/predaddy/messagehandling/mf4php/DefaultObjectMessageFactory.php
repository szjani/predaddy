<?php
declare(strict_types=1);

namespace predaddy\messagehandling\mf4php;

use mf4php\ObjectMessage;
use Serializable;

/**
 * Default ObjectMessageFactory.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class DefaultObjectMessageFactory implements ObjectMessageFactory
{
    public function createMessage(Serializable $message) : ObjectMessage
    {
        return new ObjectMessage($message);
    }
}
