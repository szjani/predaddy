<?php
declare(strict_types=1);

namespace predaddy\messagehandling\mf4php;

use mf4php\ObjectMessage;
use Serializable;

/**
 * Used to create ObjectMessage object for Message objects.
 * Useful if you want to define a delay or priority
 * for the message will be sent to mf4php dispatcher.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface ObjectMessageFactory
{
    /**
     * @param Serializable $message
     * @return ObjectMessage
     */
    public function createMessage(Serializable $message) : ObjectMessage;
}
