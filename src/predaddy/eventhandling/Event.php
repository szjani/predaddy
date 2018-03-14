<?php
declare(strict_types=1);

namespace predaddy\eventhandling;

use predaddy\messagehandling\Message;

/**
 * Base interface for all events in the application.
 * All classes that represent an event should implement this interface.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface Event extends Message
{
}
