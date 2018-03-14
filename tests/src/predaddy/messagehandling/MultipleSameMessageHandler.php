<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use predaddy\messagehandling\annotation\Subscribe;

/**
 * Description of MultipleSameMessageHandler
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class MultipleSameMessageHandler extends AbstractMessageHandler
{
    public $lastMessage2;
    public $lastMessage3;
    public $lastMessage4;

    /**
     * @Subscribe
     */
    public function handle1(SimpleMessage $message)
    {
        $this->lastMessage = $message;
    }

    /**
     * @Subscribe
     */
    public function handle2(Message $message)
    {
        $this->lastMessage2 = $message;
    }

    /**
     * @Subscribe
     */
    public function handle3(SimpleMessage $message)
    {
        $this->lastMessage3 = $message;
    }

    /**
     * @Subscribe
     */
    public function handle4(AbstractMessage $message)
    {
        $this->lastMessage4 = $message;
    }
}
