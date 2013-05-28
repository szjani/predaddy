<?php
namespace sample;

require_once __DIR__ . '/../vendor/autoload.php';

use precore\lang\Object;
use predaddy\eventhandling\DirectEventBus;
use predaddy\eventhandling\Event;
use predaddy\eventhandling\EventBase;
use predaddy\eventhandling\EventHandler;
use predaddy\eventhandling\Subscribe;

class SampleEvent1 extends EventBase
{
}

class SampleEvent2 extends EventBase
{
}

class AllEventHandler extends Object implements EventHandler
{
    /**
     * @Subscribe
     * @param \predaddy\eventhandling\Event $event
     */
    public function handleEvents(Event $event)
    {
        printf("Instance of %s has been caught\n", $event->getClassName());
    }
}

$bus = new DirectEventBus('sample3');
$bus->register(new AllEventHandler());

$bus->post(new SampleEvent1());
$bus->post(new SampleEvent2());
