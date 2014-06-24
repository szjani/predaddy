<?php
/*
 * Copyright (c) 2012-2014 Szurovecz János
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

use Doctrine\ORM\Mapping as ORM;
use precore\lang\Object;
use predaddy\domain\AggregateId;

/**
 * Class Snapshot
 *
 * @package predaddy\domain\impl\doctrine
 *
 * @author Szurovecz János <szjani@szjani.hu>
 *
 * @ORM\Entity
 * @ORM\Table(name="snapshot")
 */
class Snapshot extends Object
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
     * @ORM\Column(type="text")
     * @var string
     */
    private $data;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $version;

    /**
     * @param AggregateId $aggregateId
     * @return array Can be used in querying database
     */
    public static function createPrimaryIdArray(AggregateId $aggregateId)
    {
        return ['aggregateId' => $aggregateId->value(), 'type' => $aggregateId->aggregateClass()];
    }

    /**
     * @param string $aggregateId
     * @param string $aggregateRoot Serialized aggregate root
     * @param string $type
     * @param $version
     */
    public function __construct($aggregateId, $aggregateRoot, $type, $version)
    {
        $this->aggregateId = $aggregateId;
        $this->type = $type;
        $this->data = $aggregateRoot;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getAggregateRoot()
    {
        return $this->data;
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

    public function update($data, $version)
    {
        $this->data = $data;
        $this->version = $version;
    }
}
