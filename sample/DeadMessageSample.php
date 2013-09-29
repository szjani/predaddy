<?php
namespace sample;

require_once __DIR__ . '/../vendor/autoload.php';

use predaddy\messagehandling\DeadMessage;
use predaddy\messagehandling\MessageBase;
use predaddy\messagehandling\annotation\Subscribe;

class UnprocessedMessage extends MessageBase
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
