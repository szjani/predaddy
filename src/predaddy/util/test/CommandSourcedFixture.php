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

namespace predaddy\util\test;

use predaddy\commandhandling\Command;
use predaddy\domain\DomainEvent;
use predaddy\domain\Repository;

/**
 * Class CommandSourcedFixture
 *
 * @package predaddy\util\test
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class CommandSourcedFixture extends Fixture
{
    /**
     * @var array
     */
    private $givenCommands;

    public function __construct($aggregateClass, Repository $repository = null)
    {
        parent::__construct($aggregateClass, $repository);
        $this->givenCommands = [];
    }

    public function givenCommands(Command $commands)
    {
        $this->givenCommands = func_get_args();
        $eventHandler = function (DomainEvent $event) {
            $this->setAggregateId($event->aggregateId());
        };
        $this->getEventBus()->registerClosure($eventHandler);
        foreach ($this->givenCommands as $command) {
            $this->getCommandBus()->post($command);
        }
        $this->getEventBus()->unregisterClosure($eventHandler);
        return $this;
    }
}
