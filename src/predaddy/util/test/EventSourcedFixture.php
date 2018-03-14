<?php
declare(strict_types=1);

namespace predaddy\util\test;

use precore\util\UUID;
use predaddy\domain\AbstractDomainEvent;
use predaddy\domain\GenericAggregateId;
use predaddy\domain\DomainEvent;
use predaddy\domain\eventsourcing\EventSourcingRepository;
use predaddy\domain\EventStore;
use predaddy\domain\NullAggregateId;
use predaddy\inmemory\InMemoryEventStore;

/**
 * Class EventSourcedFixture
 *
 * @package predaddy\util\test
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class EventSourcedFixture extends Fixture
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var DomainEvent[]
     */
    private $given = [];

    public function __construct($aggregateClass)
    {
        $this->eventStore = new InMemoryEventStore();
        parent::__construct($aggregateClass, new EventSourcingRepository($this->eventStore));
    }

    /**
     * These events will be initialize the AR. It works only with ES aggregates.
     *
     * @param DomainEvent $events
     * @return EventSourcedFixture
     */
    public function givenEvents(DomainEvent $events)
    {
        $this->given = func_get_args();
        foreach ($this->given as $event) {
            if ($this->getAggregateId() === null) {
                $aggregateId = $event->aggregateId() !== null && !$event->aggregateId()->equals(NullAggregateId::instance())
                    ? $event->aggregateId()
                    : new GenericAggregateId(UUID::randomUUID()->toString(), $this->getAggregateClass());
                $this->setAggregateId($aggregateId);
            }
            if ($event instanceof AbstractDomainEvent) {
                AbstractDomainEvent::initEvent($event, $this->getAggregateId(), $event->identifier());
            }
            $this->eventStore->persist($event);
        }
        return $this;
    }
}
