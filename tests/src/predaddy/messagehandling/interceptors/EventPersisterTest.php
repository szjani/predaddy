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

namespace src\predaddy\messagehandling\interceptors;

use PHPUnit_Framework_TestCase;
use predaddy\messagehandling\InterceptorChain;
use predaddy\messagehandling\interceptors\EventPersister;

/**
 * @package src\predaddy\messagehandling\interceptors
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class EventPersisterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EventPersister
     */
    private $persister;
    private $eventStore;

    /**
     * @var InterceptorChain
     */
    private static $anyChain;

    protected function setUp()
    {
        $this->eventStore = $this->getMock('\predaddy\domain\EventStore');
        $this->persister = new EventPersister($this->eventStore);
        self::$anyChain = $this->getMock('\predaddy\messagehandling\InterceptorChain');
    }

    /**
     * @test
     * @expectedException precore\lang\ClassCastException
     */
    public function nonEventShouldCauseException()
    {
        $invalidMessage = $this;
        $this->persister->invoke($invalidMessage, self::$anyChain);
    }

    /**
     * @test
     */
    public function eventShouldBePersisted()
    {
        $event = $this->getMock('\predaddy\domain\DomainEvent');
        $chain = $this->getMock('\predaddy\messagehandling\InterceptorChain');
        $chain
            ->expects(self::once())
            ->method('proceed');
        $this->eventStore
            ->expects(self::once())
            ->method('persist')
            ->with($event);
        $this->persister->invoke($event, $chain);
    }
}
