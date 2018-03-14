<?php
declare(strict_types=1);

namespace predaddy\domain;

use precore\util\ToStringHelper;
use predaddy\messagehandling\AbstractMessage;

/**
 * Base class for all Domain Events.
 * This class contains the basic behavior expected from any event
 * to be processed by event sourcing engines and aggregates.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class AbstractDomainEvent extends AbstractMessage implements DomainEvent
{
    use StateHashTrait;

    protected $aggregateClass;
    protected $aggregateValue;

    /**
     * @param AbstractDomainEvent $event
     * @param AggregateId $aggregateId
     * @param $stateHash
     * @return AbstractDomainEvent
     */
    public static function initEvent(AbstractDomainEvent $event, AggregateId $aggregateId, $stateHash) : AbstractDomainEvent
    {
        $event->setAggregateId($aggregateId);
        $event->stateHash = $stateHash;
        return $event;
    }

    /**
     * @param AggregateId|null $aggregateId
     */
    public function __construct(AggregateId $aggregateId = null)
    {
        parent::__construct();
        if ($aggregateId === null) {
            $aggregateId = NullAggregateId::instance();
        }
        $this->setAggregateId($aggregateId);
    }

    /**
     * @return GenericAggregateId
     */
    public function aggregateId() : AggregateId
    {
        return new GenericAggregateId($this->aggregateValue, $this->aggregateClass);
    }

    protected function toStringHelper() : ToStringHelper
    {
        return parent::toStringHelper()
            ->add($this->aggregateId())
            ->add('stateHash', $this->stateHash());
    }

    /**
     * @param AggregateId $aggregateId
     */
    private function setAggregateId(AggregateId $aggregateId) : void
    {
        $this->aggregateClass = $aggregateId->aggregateClass();
        $this->aggregateValue = $aggregateId->value();
    }
}
