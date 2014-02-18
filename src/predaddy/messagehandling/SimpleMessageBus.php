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

namespace predaddy\messagehandling;

use Closure;
use EmptyIterator;
use Exception;
use Iterator;
use precore\lang\Object;
use ReflectionFunction;
use ReflectionMethod;
use SplObjectStorage;

/**
 * MessageBus which find handler methods in the registered message handlers.
 *
 * Handler method finding mechanism can be modified by the given
 * MessageHandlerDescriptorFactory and FunctionDescriptorFactory instances.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class SimpleMessageBus extends Object implements MessageBus
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var MessageHandlerDescriptorFactory
     */
    private $handlerDescriptorFactory;

    /**
     * @var SplObjectStorage
     */
    private $handlers;

    /**
     * @var SplObjectStorage
     */
    private $closures;

    /**
     * @var FunctionDescriptorFactory
     */
    private $closureDescriptorFactory;

    /**
     * @var Iterator
     */
    private $interceptors;

    /**
     * @param $identifier
     * @param MessageHandlerDescriptorFactory $handlerDescFactory
     * @param FunctionDescriptorFactory $closureDescFactory
     */
    public function __construct(
        $identifier,
        MessageHandlerDescriptorFactory $handlerDescFactory,
        FunctionDescriptorFactory $closureDescFactory
    ) {
        $this->handlers = new SplObjectStorage();
        $this->closures = new SplObjectStorage();
        $this->identifier = (string) $identifier;
        $this->handlerDescriptorFactory = $handlerDescFactory;
        $this->closureDescriptorFactory = $closureDescFactory;
        $this->interceptors = new EmptyIterator();
    }

    /**
     * @param Message $message
     * @param MessageCallback $callback
     */
    public function post(Message $message, MessageCallback $callback = null)
    {
        self::getLogger()->debug(
            "The following message has been posted to '{}' message bus: {}",
            array($this->identifier, $message)
        );
        $this->innerPost($message, $callback);
    }

    /**
     * @param Iterator $interceptors
     */
    public function setInterceptors(Iterator $interceptors)
    {
        $this->interceptors = $interceptors;
    }

    /**
     * @param object $handler
     */
    public function register($handler)
    {
        $descriptor = $this->handlerDescriptorFactory->create($handler);
        $this->handlers->attach($handler, $descriptor);
    }

    /**
     * @param object $handler
     */
    public function unregister($handler)
    {
        $this->handlers->detach($handler);
    }

    /**
     * @param callable $closure
     */
    public function registerClosure(Closure $closure)
    {
        $descriptor = $this->closureDescriptorFactory->create(new ReflectionFunction($closure));
        $this->closures->attach($closure, $descriptor);
    }

    /**
     * @param callable $closure
     */
    public function unregisterClosure(Closure $closure)
    {
        $this->closures->detach($closure);
    }

    protected function innerPost(Message $message, MessageCallback $callback = null)
    {
        $this->forwardMessage($message, $callback);
    }

    protected function forwardMessage(Message $message, MessageCallback $callback = null)
    {
        $forwarded = false;
        foreach ($this->handlers as $handler) {
            /* @var $descriptor MessageHandlerDescriptor */
            $descriptor = $this->handlers[$handler];
            $methods = $descriptor->getHandlerMethodsFor($message);
            /* @var $method ReflectionMethod */
            foreach ($methods as $method) {
                $forwarded = $this->dispatch($message, new MethodWrapper($handler, $method), $callback) || $forwarded;
            }
        }
        /* @var $descriptor FunctionDescriptor */
        foreach ($this->closures as $closure) {
            $descriptor = $this->closures[$closure];
            if ($descriptor->isHandlerFor($message)) {
                $forwarded = $this->dispatch($message, new ClosureWrapper($closure), $callback) || $forwarded;
            }
        }
        if (!$forwarded && !($message instanceof DeadMessage)) {
            self::getLogger()->debug(
                "The following message as a DeadMessage has been posted to '{}' message bus: {}",
                array($this->identifier, $message)
            );
            $this->forwardMessage(new DeadMessage($message));
        }
    }

    protected function doDispatch(Message $message, CallableWrapper $callable)
    {
        $this->interceptors->rewind();
        $chain = new DefaultInterceptorChain($message, $this->interceptors, $callable);
        return $chain->proceed();
    }

    protected function dispatch(Message $message, CallableWrapper $callable, MessageCallback $callback = null)
    {
        try {
            $result = $this->doDispatch($message, $callable);
            self::getLogger()->debug(
                "The following message has been dispatched to handler '{}' through message bus '{}': {}",
                array($callable, $this->identifier, $message)
            );
            if ($callback !== null) {
                $callback->onSuccess($result);
            }
            return true;
        } catch (Exception $e) {
            self::getLogger()->warn(
                "An error occured in the following message handler through message bus '{}': {}, message is {}!",
                array($this->identifier, $callable, $message),
                $e
            );
            if ($callback !== null) {
                $callback->onFailure($e);
            }
            return false;
        }
    }
}
