<?php
namespace sample;

use predaddy\eventhandling\DirectEventBus;
use predaddy\eventhandling\Event;
use predaddy\eventhandling\EventBase;

require_once __DIR__ . '/../vendor/autoload.php';

class SampleEvent extends EventBase
{
}

$bus = new DirectEventBus(__FILE__);
$bus->registerClosure(
    function (Event $event) {
        printf(
            "Incoming event %s sent %s\n",
            $event->getEventIdentifier(),
            $event->getTimestamp()->format('Y-m-d H:i:s')
        );
    }
);
$bus->post(new SampleEvent());
