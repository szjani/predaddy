<?php
namespace sample;

require_once __DIR__ . '/../vendor/autoload.php';

use precore\lang\Object;
use predaddy\eventhandling\DirectEventBus;
use predaddy\eventhandling\EventBase;
use predaddy\eventhandling\EventHandler;

class SampleEvent1 extends EventBase
{
}

class SampleEvent2 extends EventBase
{
}

class SampleEventHandler extends Object implements EventHandler
{
    public function handleOne(SampleEvent1 $event)
    {
        printf(
            "handleOne: Incoming event %s sent %s\n",
            $event->getEventIdentifier(),
            $event->getTimestamp()->format('Y-m-d H:i:s')
        );
    }

    public function handleTwo(SampleEvent2 $event)
    {
        printf(
            "handleTwo: Incoming event %s sent %s\n",
            $event->getEventIdentifier(),
            $event->getTimestamp()->format('Y-m-d H:i:s')
        );
    }
}

$bus = new DirectEventBus('sample2');
$bus->register(new SampleEventHandler());

$bus->post(new SampleEvent1());
$bus->post(new SampleEvent2());
$bus->post(new SampleEvent1());
$bus->post(new SampleEvent2());
