<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use DateTime;
use precore\lang\ObjectInterface;

/**
 * Base interface for messages, but it's not required to implement it.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface Message extends ObjectInterface
{
    /**
     * Returns the identifier of this message.
     *
     * @return string
     */
    public function identifier() : string;

    /**
     * Returns the timestamp of this message.
     *
     * @return DateTime
     */
    public function created() : DateTime;
}
