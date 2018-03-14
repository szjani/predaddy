<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use predaddy\messagehandling\annotation\Subscribe;

/**
 * Description of AllMessageHandler
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class AllMessageHandler extends AbstractMessageHandler
{
    /**
     * @Subscribe
     * @param Message $message
     */
    public function handle(Message $message)
    {
        $this->lastMessage = $message;
    }

    /**
     * @Subscribe
     * @param Message $message
     * @param null $param
     */
    public function invalidHandleWithParameter(Message $message, $param = null)
    {
        $this->lastMessage = $message;
    }

    /**
     * @Subscribe
     * @param $message
     */
    public function invalidHandleWithNoMessage($message)
    {
        $this->lastMessage = $message;
    }

    /**
     * @Subscribe
     * @param Message $message
     */
    private function privateHandler(Message $message)
    {
        $this->lastMessage = $message;
    }

    /**
     * @Subscribe
     * @param Message $message
     */
    private function protectedHandler(Message $message)
    {
        $this->lastMessage = $message;
    }
}
