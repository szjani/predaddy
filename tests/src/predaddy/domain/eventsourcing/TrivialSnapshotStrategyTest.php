<?php
declare(strict_types=1);

namespace predaddy\domain\eventsourcing;

use PHPUnit\Framework\TestCase;

class TrivialSnapshotStrategyTest extends TestCase
{
    const ANY_VERSION = 1;

    public function testSnapshotRequired()
    {
        $anyEvent = $this->getMockBuilder('predaddy\domain\DomainEvent')->getMock();
        self::assertTrue(TrivialSnapshotStrategy::$ALWAYS->snapshotRequired($anyEvent, self::ANY_VERSION));
        self::assertFalse(TrivialSnapshotStrategy::$NEVER->snapshotRequired($anyEvent, self::ANY_VERSION));
    }
}
