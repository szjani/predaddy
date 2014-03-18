<?php
namespace sample;

require_once __DIR__ . '/../../bootstrap.php';

use predaddy\messagehandling\DeadMessage;
use predaddy\messagehandling\AbstractMessage;
use predaddy\messagehandling\annotation\Subscribe;

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
        printf("Instance of %s has not been caught\n", $message->getMessage()->getClassName());
    }
}

$bus = require_once 'sampleBus.php';
$bus->register(new DeadMessageHandler());

$bus->post(new UnprocessedMessage());
