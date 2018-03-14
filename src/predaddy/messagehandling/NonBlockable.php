<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

/**
 * Marker interface for messages. It is a hint for message buses that the message cannot be buffered or delayed.
 *
 * @package predaddy\messagehandling
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface NonBlockable
{
}
