<?php
declare(strict_types=1);

namespace predaddy\eventhandling;

use PHPUnit\Framework\TestCase;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;

class EventBusTest extends TestCase
{
    public function testNoHandler()
    {
        $eventBus = new EventBus();

        $event = new SimpleEvent();
        $called = false;
        $eventBus->registerClosure(
            function(Event $incomingEvent) use (&$called, $event) {
                $called = true;
                EventBusTest::assertSame($event, $incomingEvent);
            }
        );
        $eventBus->post($event);
        self::assertTrue($called);
    }
}
