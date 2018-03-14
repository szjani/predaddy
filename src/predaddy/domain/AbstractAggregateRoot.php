<?php
declare(strict_types=1);

namespace predaddy\domain;

use precore\lang\BaseObject;
use precore\lang\IllegalStateException;
use precore\lang\ObjectInterface;
use precore\util\Objects;
use precore\util\Preconditions;

/**
 * AggregateRoot implementation which provides features for handling
 * state hash and sending DomainEvents.
 *
 * All Events which extends AbstractDomainEvent will be filled
 * with the proper AggregateId and state hash when they are being raised.
 *
 * If you want to use the state hash feature, you can follow two ways:
 *  - you persist the stateHash member variable
 *  - you define your own state hash field in your class. In this case you may need to override the following methods:
 *    - calculateNextStateHash()
 *    - setStateHash()
 *    - stateHash()
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class AbstractAggregateRoot extends BaseObject implements AggregateRoot
{
    /**
     * @var string
     */
    private $stateHash;

    /**
     * @param string $expectedHash
     * @throws IllegalStateException
     */
    final public function failWhenStateHashViolation($expectedHash) : void
    {
        Preconditions::checkState(
            $this->stateHash() === $expectedHash,
            'Concurrency Violation: Stale data detected. Entity was already modified.'
        );
    }

    /**
     * The basic behavior that the state hash of the AggregateRoot
     * is the ID of the last event.
     *
     * @param DomainEvent $raisedEvent
     * @return string
     */
    protected function calculateNextStateHash(DomainEvent $raisedEvent) : string
    {
        return $raisedEvent->identifier();
    }

    protected function setStateHash($stateHash) : void
    {
        $this->stateHash = $stateHash;
    }

    /**
     * Updates the state hash and sends the DomainEvent to the EventPublisher.
     * It also automatically fills the raised event if it extends AbstractDomainEvent.
     *
     * @param DomainEvent $event
     */
    final protected function raise(DomainEvent $event) : void
    {
        $this->setStateHash($this->calculateNextStateHash($event));
        if ($event instanceof AbstractDomainEvent) {
            AbstractDomainEvent::initEvent($event, $this->getId(), $this->stateHash());
        }
        EventPublisher::instance()->post($event);
    }

    /**
     * @return null|string
     */
    public function stateHash() : ?string
    {
        return $this->stateHash;
    }

    public function toString() : string
    {
        return Objects::toStringHelper($this)
            ->add('id', $this->getId())
            ->add('stateHash', $this->stateHash())
            ->toString();
    }

    public function equals(ObjectInterface $object = null) : bool
    {
        // instanceof has to be used here, since aggregates might be proxied (e.g. Doctrine)
        return $object instanceof AbstractAggregateRoot
            && Objects::equal($this->getId(), $object->getId());
    }
}
