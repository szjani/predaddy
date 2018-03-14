<?php
declare(strict_types=1);

namespace predaddy\messagehandling\configuration;

/**
 * Represents a handler method's name and priority.
 *
 * @package predaddy\messagehandling\configuration
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class MethodConfiguration
{
    private $name;
    private $priority;

    /**
     * @param string $name
     * @param int $priority
     */
    public function __construct(string $name, int $priority)
    {
        $this->name = $name;
        $this->priority = (int) $priority;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getPriority() : int
    {
        return $this->priority;
    }
}
