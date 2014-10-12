<?php
namespace sample;

require_once __DIR__ . '/../../bootstrap.php';

use predaddy\messagehandling\AbstractMessage;
use predaddy\messagehandling\annotation\Subscribe;
use predaddy\messagehandling\SimpleMessageBus;

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
            $event->identifier(),
            $event->created()->format('Y-m-d H:i:s')
        );
    }

    /**
     * @Subscribe
     */
    public function handleTwo(SampleMessage2 $event)
    {
        printf(
            "handleTwo: Incoming message %s sent %s\n",
            $event->identifier(),
            $event->created()->format('Y-m-d H:i:s')
        );
    }
}

$bus = new SimpleMessageBus();
$bus->register(new SampleMessageHandler());

$bus->post(new SampleMessage1());
$bus->post(new SampleMessage2());
$bus->post(new SampleMessage1());
$bus->post(new SampleMessage2());
