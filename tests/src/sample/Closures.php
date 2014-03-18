<?php
namespace sample;

require_once __DIR__ . '/../../bootstrap.php';

use precore\util\error\ErrorHandler;

// if the message object cannot be cast to string which is required in lf4php, it can be handled by registering
// an error handler which converts all errors to exception
ErrorHandler::register();

class SampleMessage
{
}

$bus = require_once 'sampleBus.php';

$bus->registerClosure(
    function (SampleMessage $message) {
        printf(
            "Incoming message\n"
        );
    }
);
$bus->post(new SampleMessage());
