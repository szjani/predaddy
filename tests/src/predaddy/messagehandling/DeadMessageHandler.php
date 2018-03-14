<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use predaddy\messagehandling\annotation\Subscribe;

/**
 * Description of DeadMessageHandler
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class DeadMessageHandler extends AbstractMessageHandler
{
    /**
     * @Subscribe
     * @param DeadMessage $message
     */
    public function handle(DeadMessage $message)
    {
        $this->lastMessage = $message;
    }
}
