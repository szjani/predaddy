<?php
declare(strict_types=1);

namespace predaddy\domain;

use PHPUnit\Framework\TestCase;

/**
 * @package predaddy\domain
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class AbstractDomainEventTest extends TestCase
{
    public function testEmptyConstructorResult()
    {
        $event = new DecrementedEvent(1);
        self::assertNull($event->stateHash());
        self::assertEquals('', $event->aggregateId()->value());
    }
}
