<?php
namespace sample;

require_once __DIR__ . '/../../bootstrap.php';

use predaddy\messagehandling\DeadMessage;
use predaddy\messagehandling\AbstractMessage;
use predaddy\messagehandling\annotation\Subscribe;
use predaddy\messagehandling\SimpleMessageBus;

class UnprocessedMessage extends AbstractMessage
{
}

class DeadMessageHandler
{
    /**
     * @Subscribe
     */
    public function handleDeadEvents(DeadMessage $message)
    {
        printf("Instance of %s has not been caught\n", $message->wrappedMessage()->getClassName());
    }
}

$bus = new SimpleMessageBus();
$bus->register(new DeadMessageHandler());

$bus->post(new UnprocessedMessage());
