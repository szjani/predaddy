<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use predaddy\messagehandling\annotation\Subscribe;

/**
 * Description of SimpleMessageHandler
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class SimpleMessageHandler extends AbstractMessageHandler
{
    /**
     * @Subscribe
     * @param SimpleMessage $message
     */
    public function handle(SimpleMessage $message)
    {
        $this->lastMessage = $message;
    }

    /**
     * @Subscribe
     * @param SimpleMessageHandler $handler
     */
    public function handleSelf(SimpleMessageHandler $handler)
    {
    }
}
