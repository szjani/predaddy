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

namespace predaddy\messagehandling\util;

use Closure;
use predaddy\messagehandling\MessageBus;
use predaddy\messagehandling\MessageCallback;

/**
 * @package predaddy\messagehandling\util
 *
 * @author Szurovecz János <szjani@szjani.hu>
 * @codeCoverageIgnore
 */
final class NullMessageBus implements MessageBus
{
    /**
     * Post a message on this bus. It is dispatched to all subscribed handlers.
     * MessageCallback will be notified with each message handler calls.
     *
     * MessageCallback is not necessarily supported by all implementations!
     *
     * @param object $message
     * @param MessageCallback $callback
     * @return void
     * @throws \InvalidArgumentException If $message is not an object
     */
    public function post($message, MessageCallback $callback = null)
    {
    }

    /**
     * Register the given handler to this bus. When registered, it will receive all messages posted to this bus.
     *
     * @param mixed $handler
     * @return void
     */
    public function register($handler)
    {
    }

    /**
     * Un-register the given handler to this bus.
     * When unregistered, it will no longer receive messages posted to this bus.
     *
     * @param object $handler
     * @return void
     */
    public function unregister($handler)
    {
    }

    /**
     * @param Closure $closure
     * @param int $priority Used to sort handlers when dispatching messages
     * @return void
     */
    public function registerClosure(Closure $closure, $priority = self::DEFAULT_PRIORITY)
    {
    }

    /**
     * @param Closure $closure
     * @return void
     */
    public function unregisterClosure(Closure $closure)
    {
    }
}
