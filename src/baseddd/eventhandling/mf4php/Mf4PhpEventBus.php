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

namespace baseddd\eventhandling\mf4php;

use baseddd\eventhandling\AbstractEventBus;
use baseddd\eventhandling\Event;
use baseddd\eventhandling\EventHandler;
use mf4php\DefaultQueue;
use mf4php\Message;
use mf4php\MessageDispatcher;
use mf4php\MessageListener;
use mf4php\ObjectMessage;

/**
 * EventBus implementation which uses mf4php to forward events.
 *
 * If you use a proper MessageDispatcher it is possible to
 * handle events after the transaction has been committed.
 *
 * With an asynchronous MessageDispatcher implementation event
 * handling can be asynchronous.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class Mf4PhpEventBus extends AbstractEventBus implements MessageListener
{
    private $dispatcher;
    private $queue;

    public function __construct($identifier, MessageDispatcher $dispatcher)
    {
        parent::__construct($identifier);
        $this->dispatcher = $dispatcher;
        $this->queue = new DefaultQueue($identifier);
    }

    protected function callHandlerMethod(EventHandler $handler, $method, Event $event)
    {
        $message = new ObjectMessage(new EventWrapper($event, $handler->getClassName(), $method));
        $this->dispatcher->send($this->queue, $message);
    }

    public function onMessage(Message $message)
    {
        /* @var $message ObjectMessage */
        $eventWrapper = $message->getObject();
        /* @var $eventWrapper EventWrapper */
        if ($eventWrapper instanceof EventWrapper) {
            $handler = $this->getHandler($eventWrapper->getHandlerClass());
            if ($handler !== null) {
                $method = $eventWrapper->getHandlerMethod();
                $handler->$method($eventWrapper->getEvent());
            }
        }
    }
}
