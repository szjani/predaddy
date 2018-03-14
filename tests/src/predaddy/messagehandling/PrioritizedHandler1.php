<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use predaddy\messagehandling\annotation\Subscribe;

class PrioritizedHandler1
{
    private $order;

    public function __construct(array &$order)
    {
        $this->order = &$order;
    }

    /**
     * @Subscribe
     */
    public function handler1(Message $message)
    {
        $this->order[] = 1;
    }

    /**
     * @Subscribe(priority=2)
     */
    public function handler2(Message $message)
    {
        $this->order[] = 2;
    }
}
