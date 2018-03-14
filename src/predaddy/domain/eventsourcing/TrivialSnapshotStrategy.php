<?php
declare(strict_types=1);

namespace predaddy\domain\eventsourcing;

use precore\lang\Enum;
use predaddy\domain\DomainEvent;

final class TrivialSnapshotStrategy extends Enum implements SnapshotStrategy
{
    /**
     * @var TrivialSnapshotStrategy
     */
    public static $ALWAYS;

    /**
     * @var TrivialSnapshotStrategy
     */
    public static $NEVER;

    private $snapshotRequired;

    protected static function constructorArgs()
    {
        return [
            'ALWAYS' => [true],
            'NEVER' => [false]
        ];
    }

    protected function __construct($snapshotRequired)
    {
        $this->snapshotRequired = $snapshotRequired;
    }

    /**
     * @param DomainEvent $event
     * @param int|null $originalVersion
     * @return boolean
     */
    public function snapshotRequired(DomainEvent $event, ?int $originalVersion) : bool
    {
        return $this->snapshotRequired;
    }
}
TrivialSnapshotStrategy::init();
