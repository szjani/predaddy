<?php
namespace sample;

require_once __DIR__ . '/../../bootstrap.php';

use predaddy\messagehandling\AbstractMessage;
use predaddy\messagehandling\annotation\Subscribe;

class SampleMessage1 extends AbstractMessage
{
}

class SampleMessage2 extends AbstractMessage
{
}

class SampleMessageHandler
{
    /**
     * @Subscribe
     */
    public function handleOne(SampleMessage1 $event)
    {
        printf(
            "handleOne: Incoming message %s sent %s\n",
            $event->getMessageIdentifier(),
            $event->getTimestamp()->format('Y-m-d H:i:s')
        );
    }

    /**
     * @Subscribe
     */
    public function handleTwo(SampleMessage2 $event)
    {
        printf(
            "handleTwo: Incoming message %s sent %s\n",
            $event->getMessageIdentifier(),
            $event->getTimestamp()->format('Y-m-d H:i:s')
        );
    }
}

$bus = require_once 'sampleBus.php';
$bus->register(new SampleMessageHandler());

$bus->post(new SampleMessage1());
$bus->post(new SampleMessage2());
$bus->post(new SampleMessage1());
$bus->post(new SampleMessage2());
