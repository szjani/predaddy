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
use precore\util\Objects;
use predaddy\domain\AggregateId;
use Doctrine\ORM\Mapping as ORM;
use predaddy\domain\DomainEvent;

/**
 * Meta class which stores DomainEvent object.
 *
 * @package predaddy\domain\eventstore\doctrine
 *
 * @author Szurovecz János <szjani@szjani.hu>
 *
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(
 *   name="event",
 *   indexes={
 *     @ORM\Index(name="event_aggregate_id_type", columns={"aggregate_id", "aggregate_type"}),
 *     @ORM\Index(name="event_created", columns={"created"}),
 *     @ORM\Index(name="event_version", columns={"version"})
 *   }
 * )
 */
class Event extends Object
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="sequence_number")
     * @ORM\GeneratedValue
     * @var int
     */
    private $sequenceNumber;

    /**
     * @ORM\Column(type="string", name="aggregate_id")
     * @var string
     */
    private $aggregateId;

    /**
     * @ORM\Column(type="string", name="aggregate_type")
     * @var string
     */
    private $aggregateType;

    /**
     * @ORM\Column(type="string", name="event_type")
     * @var string
     */
    private $eventType;

    /**
     * @ORM\Column(type="integer", name="version")
     * @var int
     */
    private $version;

    /**
     * @ORM\Column(type="text", nullable=false)
     * @var string
     */
    private $data;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @var DateTime
     */
    private $created;

    /**
     * @param AggregateId $aggregateId
     * @param string $aggregateType
     * @param \predaddy\domain\DomainEvent $event
     * @param string $serializedEvent
     */
    public function __construct(AggregateId $aggregateId, $aggregateType, DomainEvent $event, $serializedEvent)
    {
        $this->aggregateType = $aggregateType;
        $this->data = $serializedEvent;
        $this->aggregateId = $aggregateId->getValue();
        $this->version = $event->getVersion();
        $this->created = clone $event->getTimestamp();
        $this->eventType = $event->getClassName();
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
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return DateTime
     */
    public function getCreated()
    {
        return clone $this->created;
    }

    public function toString()
    {
        return Objects::toStringHelper($this)
            ->add('aggregateId', $this->aggregateId)
            ->add('aggregateType', $this->aggregateType)
            ->add('version', $this->version)
            ->add('created', $this->created)
            ->add('data', $this->data)
            ->toString();
    }

    /**
     * @return int
     */
    public function getSequenceNumber()
    {
        return $this->sequenceNumber;
    }

    /**
     * @return string
     */
    public function getAggregateType()
    {
        return $this->aggregateType;
    }

    /**
     * @return string
     */
    public function getEventType()
    {
        return $this->eventType;
    }
}
