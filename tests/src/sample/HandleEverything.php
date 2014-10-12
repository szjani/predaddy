<?php
namespace sample;

require_once __DIR__ . '/../../bootstrap.php';

use predaddy\messagehandling\Message;
use predaddy\messagehandling\AbstractMessage;
use predaddy\messagehandling\annotation\Subscribe;
use predaddy\messagehandling\SimpleMessageBus;

class SampleMessage1 extends AbstractMessage
{
}

class SampleMessage2 extends AbstractMessage
{
}

class AllMessageHandler
{
    /**
     * @Subscribe
     */
    public function handleEvents(Message $message)
    {
        printf("Instance of %s has been caught\n", $message->getClassName());
    }
}

$bus = new SimpleMessageBus();
$bus->register(new AllMessageHandler());

$bus->post(new SampleMessage1());
$bus->post(new SampleMessage2());
