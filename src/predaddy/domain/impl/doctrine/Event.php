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

use precore\lang\Object;
use predaddy\domain\AggregateId;
use predaddy\domain\DomainEvent;
use Doctrine\ORM\Mapping as ORM;

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
 *     @ORM\Index(name="event_aggregate_id_version", columns={"aggregate_id", "version"}),
 *     @ORM\Index(name="event_created", columns={"created"})
 *   }
 * )
 */
class Event extends Object
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", name="event_id")
     * @var string
     */
    private $eventId;

    /**
     * @ORM\Column(type="string", name="aggregate_id", nullable=false)
     * @var string
     */
    private $aggregateId;

    /**
     * @ORM\Column(type="text", nullable=false)
     * @var string
     */
    private $data;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @var \DateTime
     */
    private $created;

    /**
     * @ORM\Column(type="integer", name="version", nullable=false)
     * @var int
     */
    private $version;

    public function __construct(DomainEvent $event, AggregateId $aggregateId, $aggregateVersion)
    {
        $this->eventId = $event->getEventIdentifier();
        $this->data = serialize($event);
        $this->aggregateId = $aggregateId->getValue();
        $this->version = $aggregateVersion;
        $this->created = $event->getTimestamp();
    }

    /**
     * @return string
     */
    public function getAggregateId()
    {
        return $this->aggregateId;
    }

    /**
     * @return DomainEvent
     */
    public function getData()
    {
        return unserialize($this->data);
    }

    /**
     * @return string
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }
}
