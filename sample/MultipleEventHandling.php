<?php
namespace sample;

require_once __DIR__ . '/../vendor/autoload.php';

use precore\lang\Object;
use predaddy\eventhandling\DirectEventBus;
use predaddy\eventhandling\EventBase;
use predaddy\eventhandling\EventHandler;
use predaddy\eventhandling\Subscribe;

class SampleEvent1 extends EventBase
{
}

class SampleEvent2 extends EventBase
{
}

class SampleEventHandler extends Object implements EventHandler
{
    /**
     * @Subscribe
     * @param \sample\SampleEvent1 $event
     */
    public function handleOne(SampleEvent1 $event)
    {
        printf(
            "handleOne: Incoming event %s sent %s\n",
            $event->getEventIdentifier(),
            $event->getTimestamp()->format('Y-m-d H:i:s')
        );
    }

    /**
     * @Subscribe
     * @param \sample\SampleEvent2 $event
     */
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
