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

namespace predaddy\eventhandling;

/**
 * @author Szurovecz János <szjani@szjani.hu>
 */
interface EventBus
{
    /**
     * Post an event on this bus. It is dispatched to all subscribed event handlers.
     *
     * @param Event $event
     */
    public function post(Event $event);

    /**
     * Register the given handler to this bus. When registered, it will receive all events posted to this bus.
     *
     * @param EventHandler $handler
     */
    public function register(EventHandler $handler);

    /**
     * Unregister the given handler to this bus.
     * When unregistered, it will no longer receive events posted to this bus.
     *
     * @param EventHandler $handler
     */
    public function unregister(EventHandler $handler);
}
