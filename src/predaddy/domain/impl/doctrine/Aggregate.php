<?php
/*
 * Copyright (c) 2014 Szurovecz János
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

namespace predaddy\domain\impl\doctrine;

use DateTime;
use precore\lang\Object;
use Doctrine\ORM\Mapping as ORM;
use predaddy\domain\AggregateId;
use predaddy\domain\DefaultAggregateId;
use predaddy\domain\DomainEvent;
use UnexpectedValueException;

/**
 * Meta entity to store aggregate roots. It supports snapshotting.
 *
 * @package predaddy\domain\eventstore\doctrine
 *
 * @author Szurovecz János <szjani@szjani.hu>
 *
 * @ORM\Entity
 * @ORM\Table(name="aggregate")
 */
class Aggregate extends Object
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", name="aggregate_id")
     * @var string
     */
    private $aggregateId;

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * @var string
     */
    private $type;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    private $updated;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Version
     * @var int
     */
    private $version = 0;

    /**
     * @param AggregateId $aggregateId
     * @return array Can be used in querying database
     */
    public static function createPrimaryIdArray(AggregateId $aggregateId)
    {
        return ['aggregateId' => $aggregateId->value(), 'type' => $aggregateId->aggregateClass()];
    }

    /**
     * @param AggregateId $aggregateId
     */
    public function __construct(AggregateId $aggregateId)
    {
        $this->aggregateId = $aggregateId->value();
        $this->type = $aggregateId->aggregateClass();
    }

    /**
     * @param DomainEvent $event
     * @param $serializedEvent
     * @throws \UnexpectedValueException
     * @return Event
     */
    public function createMetaEvent(DomainEvent $event, $serializedEvent)
    {
        $this->updated = clone $event->created();
        $aggregateId = new DefaultAggregateId($this->getAggregateId(), $this->getType());
        if (!$aggregateId->equals($event->aggregateId())) {
            throw new UnexpectedValueException(
                sprintf(
                    "Event with AggregateId '%s' cannot be stored to aggregate [type:'%s', id:'%s']",
                    $event->aggregateId(),
                    $this->type,
                    $this->aggregateId
                )
            );
        }
        return new Event($event, $serializedEvent);
    }

    /**
     * @param string $serialized
     * @return Snapshot
     */
    public function createSnapshot($serialized)
    {
        return new Snapshot($this->aggregateId, $serialized, $this->type, $this->version);
    }

    public function updateSnapshot(Snapshot $existing, $serialized)
    {
        $existing->update($serialized, $this->version);
    }

    /**
     * @return string
     */
    public function getAggregateId()
    {
        return $this->aggregateId;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
}
