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

use DateTime;
use InvalidArgumentException;
use LazyMapTestAsset\NullLazyMap;
use precore\util\Objects;
use predaddy\eventhandling\AbstractEvent;

/**
 * Base class for all Domain Events.
 * This class contains the basic behavior expected from any event
 * to be processed by event sourcing engines and aggregates.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
abstract class AbstractDomainEvent extends AbstractEvent implements DomainEvent
{
    protected $aggregateId;
    protected $version;

    public function __construct(AggregateId $aggregateId = null, $originatedVersion = null)
    {
        parent::__construct();
        if ($aggregateId === null) {
            $aggregateId = new NullAggregateId();
        }
        $this->aggregateId = $aggregateId;
        $this->version = $this->calculateNewVersion($originatedVersion);
    }

    protected function calculateNewVersion($originatedVersion)
    {
        return $originatedVersion + 1;
    }

    /**
     * @return AggregateId
     */
    public function getAggregateId()
    {
        return $this->aggregateId;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return (int) $this->version;
    }

    protected function toStringHelper()
    {
        return parent::toStringHelper()
            ->add('aggregateId', $this->getAggregateId())
            ->add('version', $this->getVersion());
    }
}
