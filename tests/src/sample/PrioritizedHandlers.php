<?php
namespace sample;

require_once __DIR__ . '/../../bootstrap.php';

use predaddy\messagehandling\AbstractMessage;
use predaddy\messagehandling\annotation\Subscribe;

class SampleMessage extends AbstractMessage
{
}

class SampleMessageHandler
{
    /**
     * @Subscribe(priority=-2)
     */
    public function handleOne(SampleMessage $event)
    {
        echo __METHOD__ . "\n";
    }

    /**
     * @Subscribe
     */
    public function handleTwo(SampleMessage $event)
    {
        echo __METHOD__ . "\n";
    }
}

$bus = require_once 'sampleBus.php';
$bus->register(new SampleMessageHandler());
$closure = function (SampleMessage $msg) {
    echo __FUNCTION__ . "\n";
};
$bus->registerClosure($closure, 8);

$bus->post(new SampleMessage());

/*
 * The output is:
 *
 * sample\{closure}
 * sample\SampleMessageHandler::handleTwo
 * sample\SampleMessageHandler::handleOne
 */
