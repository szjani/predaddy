<?php
namespace sample;

require_once __DIR__ . '/../vendor/autoload.php';

use predaddy\messagehandling\Message;
use predaddy\messagehandling\MessageBase;
use predaddy\messagehandling\annotation\Subscribe;

class SampleMessage1 extends MessageBase
{
}

class SampleMessage2 extends MessageBase
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

$bus = require_once 'sampleBus.php';
$bus->register(new AllMessageHandler());

$bus->post(new SampleMessage1());
$bus->post(new SampleMessage2());
