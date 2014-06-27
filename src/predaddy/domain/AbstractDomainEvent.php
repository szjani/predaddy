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

use predaddy\messagehandling\AbstractMessage;

/**
 * Base class for all Domain Events.
 * This class contains the basic behavior expected from any event
 * to be processed by event sourcing engines and aggregates.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
abstract class AbstractDomainEvent extends AbstractMessage implements DomainEvent
{
    protected $aggregateClass;
    protected $aggregateValue;
    protected $stateHash;

    public static function initEvent(AbstractDomainEvent $event, AggregateId $aggregateId, $stateHash)
    {
        $event->aggregateClass = $aggregateId->aggregateClass();
        $event->aggregateValue = $aggregateId->value();
        $event->stateHash = $stateHash;
    }

    public function __construct(AggregateId $aggregateId = null)
    {
        parent::__construct();
        if ($aggregateId === null) {
            $aggregateId = new NullAggregateId();
        }
        $this->aggregateClass = $aggregateId->aggregateClass();
        $this->aggregateValue = $aggregateId->value();
    }

    /**
     * @return DefaultAggregateId
     */
    public function aggregateId()
    {
        return new DefaultAggregateId($this->aggregateValue, $this->aggregateClass);
    }

    /**
     * @return string|null
     */
    public function stateHash()
    {
        return $this->stateHash;
    }

    protected function toStringHelper()
    {
        return parent::toStringHelper()
            ->add('aggregateClass', $this->aggregateValue)
            ->add('aggregateValue', $this->aggregateValue);
    }
}
