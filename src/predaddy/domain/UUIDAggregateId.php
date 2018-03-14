<?php
declare(strict_types=1);

namespace predaddy\domain;

use precore\util\UUID;

/**
 * Provides an easy way to generate unique IDs.
 *
 * It can be extended and only the aggregateClass() method must be implemented.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class UUIDAggregateId extends AbstractAggregateId
{
    /**
     * @var string
     */
    private $uuid;

    final private function __construct(string $uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return static
     */
    final public static function create() : self
    {
        return new static(UUID::randomUUID()->toString());
    }

    /**
     * @param string $value
     * @return static
     */
    final public static function from($value) : self
    {
        return new static(UUID::fromString($value)->toString());
    }

    /**
     * @return string
     */
    final public function value() : string
    {
        return $this->uuid;
    }
}
