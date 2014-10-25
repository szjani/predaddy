<?php
/*
 * Copyright (c) 2012-2014 Janos Szurovecz
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

namespace predaddy\messagehandling;

use ArrayIterator;
use Closure;
use InvalidArgumentException;
use Iterator;
use precore\lang\Object;
use predaddy\messagehandling\util\MessageCallbackClosures;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class InterceptableMessageBus extends Object implements MessageBus
{
    /**
     * @var MessageCallback
     */
    private static $emptyCallback;

    /**
     * @var DispatchInterceptor[]
     */
    private $interceptors;

    /**
     * Should not be called directly!
     */
    public static function init()
    {
        self::$emptyCallback = MessageCallbackClosures::builder()->build();
    }

    public function __construct(array $interceptors = [])
    {
        $this->interceptors = $interceptors;
    }

    /**
     * Dispatches the message to all handlers.
     *
     * All exceptions thrown by handlers must be caught and should be forwarded to $callback.
     *
     * @param $message
     * @param MessageCallback $callback
     * @return void
     */
    abstract protected function dispatch($message, MessageCallback $callback);

    /**
     * Post a message on this bus. It is dispatched to all subscribed handlers.
     * MessageCallback will be notified with each message handler calls.
     *
     * MessageCallback is not necessarily supported by all implementations!
     *
     * @param object $message
     * @param MessageCallback $callback
     * @return void
     * @throws InvalidArgumentException If $message is not an object
     */
    final public function post($message, MessageCallback $callback = null)
    {
        if (!is_object($message)) {
            self::getLogger()->warn('Incoming message is not an object');
            throw new InvalidArgumentException('Message must be an object!');
        }
        if ($callback === null) {
            $callback = self::emptyCallback();
        }
        $dispatchClosure = function () use ($message, $callback) {
            $this->dispatch($message, $callback);
        };
        $this->createChain($message, $dispatchClosure)->proceed();
    }

    /**
     * @return MessageCallback
     */
    final protected static function emptyCallback()
    {
        return self::$emptyCallback;
    }

    /**
     * @return Iterator of Interceptor
     */
    protected function createInterceptorIterator()
    {
        return new ArrayIterator($this->interceptors);
    }

    /**
     * @param $message
     * @param Closure $dispatchClosure
     * @return InterceptorChain
     */
    protected function createChain($message, Closure $dispatchClosure)
    {
        return new DefaultInterceptorChain(
            $message,
            $this->createInterceptorIterator(),
            $dispatchClosure
        );
    }
}
InterceptableMessageBus::init();
