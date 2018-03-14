<?php
declare(strict_types=1);

namespace predaddy\domain;

use PHPUnit\Framework\TestCase;
use predaddy\eventhandling\EventBus;
use predaddy\fixture\BaseEvent;

/**
 * Class EventPublisherTest
 *
 * @package predaddy\domain
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class EventPublisherTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSetNullMessageBus()
    {
        $called = false;
        $bus = new EventBus();
        $bus->registerClosure(
            function (BaseEvent $event) use (&$called) {
                $called = true;
            }
        );
        EventPublisher::instance()->setEventBus($bus);
        EventPublisher::instance()->setEventBus(null);
        EventPublisher::instance()->post(new BaseEvent());
        self::assertFalse($called);
    }
}
