<?php
declare(strict_types=1);

namespace predaddy;

use predaddy\eventhandling\Event;
use predaddy\messagehandling\Message;
use predaddy\messagehandling\annotation\Subscribe;

/**
 * @package predaddy
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class MessageHandler
{
    private $called = 0;
    private $lastMessage;

    /**
     * @Subscribe
     * @param Event $event
     */
    public function handleEvent(Event $event)
    {
        $this->called++;
        $this->lastMessage = $event;
    }

    public function called($times)
    {
        return $this->called === $times;
    }

    public function neverCalled()
    {
        return $this->called(0);
    }

    /**
     * @return Message
     */
    public function lastMessage()
    {
        return $this->lastMessage;
    }

    public function lastlyHandled(Message $message)
    {
        return $this->lastMessage()->equals($message);
    }
}
