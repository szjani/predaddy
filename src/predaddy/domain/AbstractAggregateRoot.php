<?php
/*
 * Copyright (c) 2013 Szurovecz János
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace predaddy\domain;

use precore\lang\Object;
use precore\lang\ObjectInterface;
use precore\util\Objects;
use UnexpectedValueException;
use predaddy\messagehandling\annotation\Subscribe;

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
 * @author Szurovecz János <szjani@szjani.hu>
 */
abstract class AbstractAggregateRoot extends Object implements AggregateRoot
{
    /**
     * @var string
     */
    private $stateHash;

    /**
     * @param string $expectedHash
     * @throws UnexpectedValueException
     */
    final public function failWhenStateHashViolation($expectedHash)
    {
        if ($expectedHash !== $this->stateHash()) {
            throw new UnexpectedValueException(
                'Concurrency Violation: Stale data detected. Entity was already modified.'
            );
        }
    }

    /**
     * @param DomainEvent $raisedEvent
     * @return string
     */
    protected function calculateNextStateHash(DomainEvent $raisedEvent)
    {
        return $raisedEvent->identifier();
    }

    protected function setStateHash($stateHash)
    {
        $this->stateHash = $stateHash;
    }

    /**
     * Updates stateHash field when replaying events. Should not be called directly.
     *
     * @Subscribe
     * @param DomainEvent $event
     */
    final protected function updateStateHash(DomainEvent $event)
    {
        $this->setStateHash($event->stateHash());
    }

    final protected function raise(DomainEvent $event)
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
    public function stateHash()
    {
        return $this->stateHash;
    }

    public function toString()
    {
        return Objects::toStringHelper($this)
            ->add('id', $this->getId())
            ->add('stateHash', $this->stateHash())
            ->toString();
    }

    public function equals(ObjectInterface $object = null)
    {
        return $object instanceof self
            && Objects::equal($this->getId(), $object->getId());
    }
}
