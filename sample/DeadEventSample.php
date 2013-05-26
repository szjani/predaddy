<?php
namespace sample;

require_once __DIR__ . '/../vendor/autoload.php';

use precore\lang\Object;
use predaddy\eventhandling\DeadEvent;
use predaddy\eventhandling\DirectEventBus;
use predaddy\eventhandling\EventBase;
use predaddy\eventhandling\EventHandler;

class UnprocessedEvent extends EventBase
{
}

class DeadEventHandler extends Object implements EventHandler
{
    public function handleDeadEvents(DeadEvent $event)
    {
        printf("Instance of %s has not been caught\n", $event->getEvent()->getClassName());
    }
}

$bus = new DirectEventBus('sample2');
$bus->register(new DeadEventHandler());

$bus->post(new UnprocessedEvent());
