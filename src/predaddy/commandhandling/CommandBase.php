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

namespace predaddy\commandhandling;

use predaddy\domain\AggregateId;
use predaddy\domain\DefaultAggregateId;
use predaddy\messagehandling\MessageBase;

/**
 * Base class for all types of commands. Contains the command identifier and timestamp.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
abstract class CommandBase extends MessageBase implements Command
{
    /**
     * @var string
     */
    private $aggregateId;

    /**
     * @var int
     */
    private $version;

    /**
     * @param string $aggregateId
     * @param int $version
     */
    public function __construct($aggregateId, $version = 0)
    {
        $this->aggregateId = $aggregateId;
        $this->version = $version;
    }

    /**
     * Returns the identifier of this command.
     *
     * @return string
     */
    public function getCommandIdentifier()
    {
        return $this->getMessageIdentifier();
    }

    /**
     * @return AggregateId
     */
    public function getAggregateId()
    {
        return new DefaultAggregateId($this->aggregateId);
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
}
