<?php
declare(strict_types=1);

namespace predaddy\eventhandling;

use PHPUnit\Framework\TestCase;
use predaddy\fixture\BaseEvent;
use predaddy\fixture\BaseEvent2;

/**
 * @package predaddy\eventhandling
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class AbstractDomainEventTest extends TestCase
{
    public function testEquals()
    {
        $event1 = new BaseEvent();
        $event2 = new BaseEvent2();
        self::assertFalse($event1->equals($event2));

        $clone = clone $event1;
        self::assertTrue($event1->equals($clone));
    }
}
