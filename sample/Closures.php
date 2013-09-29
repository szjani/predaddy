<?php
namespace sample;

use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\DefaultFunctionDescriptorFactory;
use predaddy\messagehandling\Message;
use predaddy\messagehandling\MessageBase;
use predaddy\messagehandling\SimpleMessageBus;

require_once __DIR__ . '/../vendor/autoload.php';

class SampleMessage extends MessageBase
{
}

$bus = require_once 'sampleBus.php';

$bus->registerClosure(
    function (Message $message) {
        printf(
            "Incoming message %s sent %s\n",
            $message->getMessageIdentifier(),
            $message->getTimestamp()->format('Y-m-d H:i:s')
        );
    }
);
$bus->post(new SampleMessage());
