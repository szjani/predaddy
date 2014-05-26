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

/**
 * Aggregate root class.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
abstract class AbstractAggregateRoot extends Object implements StateHashAwareAggregateRoot
{
    /**
     * Must be persisted.
     * @var string
     */
    protected $stateHash;

    public function toString()
    {
        return Objects::toStringHelper($this)
            ->add('id', $this->getId())
            ->toString();
    }

    public function equals(ObjectInterface $object = null)
    {
        return $object instanceof self
            && Objects::equal($this->getId(), $object->getId());
    }

    protected function raise(DomainEvent $event)
    {
        if ($event instanceof AbstractDomainEvent) {
            AbstractDomainEvent::initEvent($event, $this->getId(), $this->getClassName());
        }
        $this->stateHash = $event->getStateHash();
        EventPublisher::instance()->post($event);
    }

    /**
     * @param string $expectedHash
     * @throws UnexpectedValueException
     */
    public function failWhenStateHashViolation($expectedHash)
    {
        if ($expectedHash !== $this->stateHash) {
            throw new UnexpectedValueException(
                'Concurrency Violation: Stale data detected. Entity was already modified.'
            );
        }
    }

    /**
     * @return null|string
     */
    public function getStateHash()
    {
        return $this->stateHash;
    }
}
