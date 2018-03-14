<?php
declare(strict_types=1);

namespace predaddy\domain;

/**
 * @package predaddy\domain
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class DecrementedEvent extends AbstractDomainEvent
{
    /**
     * @var int
     */
    private $newValue;

    /**
     * @param int $newValue
     */
    public function __construct($newValue)
    {
        parent::__construct();
        $this->newValue = $newValue;
    }

    /**
     * @return int
     */
    public function getNewValue()
    {
        return $this->newValue;
    }
}
