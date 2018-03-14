<?php
declare(strict_types=1);

namespace predaddy;

use predaddy\eventhandling\Event;
use predaddy\messagehandling\annotation\Subscribe;

/**
 * @package predaddy
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class EventCollector
{
    private $events = [];

    /**
     * @Subscribe
     * @param Event $object
     */
    public function collect(Event $object)
    {
        $this->events[] = $object;
    }

    /**
     * @return Event[]
     */
    public function events()
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }
}
