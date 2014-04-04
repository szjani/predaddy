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
use precore\lang\ObjectClass;
use ReflectionFunction;
use ReflectionMethod;
use RuntimeException;
use SplObjectStorage;
use SplPriorityQueue;

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
    const DEFAULT_NAME = 'default-bus';

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
     * @param MessageHandlerDescriptorFactory $handlerDescFactory
     * @param $identifier
     */
    public function __construct(
        MessageHandlerDescriptorFactory $handlerDescFactory,
        $identifier = self::DEFAULT_NAME
    ) {
        $this->handlers = new SplObjectStorage();
        $this->closures = new SplObjectStorage();
        $this->identifier = (string) $identifier;
        $this->handlerDescriptorFactory = $handlerDescFactory;
        $this->closureDescriptorFactory = $handlerDescFactory->getFunctionDescriptorFactory();
        $this->interceptors = new EmptyIterator();
    }

    /**
     * @param object $message
     * @param MessageCallback $callback
     * @throws RuntimeException
     */
    public function post($message, MessageCallback $callback = null)
    {
        if (!is_object($message)) {
            throw new RuntimeException('Message must be an object!');
        }
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
     * @param Closure $closure
     * @param int $priority
     */
    public function registerClosure(Closure $closure, $priority = self::DEFAULT_PRIORITY)
    {
        $descriptor = $this->closureDescriptorFactory->create(new ReflectionFunction($closure), $priority);
        $this->closures->attach($closure, $descriptor);
    }

    /**
     * @param Closure $closure
     */
    public function unregisterClosure(Closure $closure)
    {
        $this->closures->detach($closure);
    }

    protected function innerPost($message, MessageCallback $callback = null)
    {
        $this->forwardMessage($message, $callback);
    }

    protected function forwardMessage($message, MessageCallback $callback = null)
    {
        $forwarded = false;
        $objectClass = ObjectClass::forName(get_class($message));
        $callbackQueue = new SplPriorityQueue();
        foreach ($this->handlers as $handler) {
            /* @var $descriptor MessageHandlerDescriptor */
            $descriptor = $this->handlers[$handler];
            /* @var $functionDescriptor FunctionDescriptor */
            foreach ($descriptor->getFunctionDescriptorsFor($objectClass) as $functionDescriptor) {
                $callbackQueue->insert(
                    new MethodWrapper($handler, $functionDescriptor->getReflectionFunction()),
                    $functionDescriptor->getPriority()
                );
            }
        }
        /* @var $descriptor FunctionDescriptor */
        foreach ($this->closures as $closure) {
            $descriptor = $this->closures[$closure];
            if ($descriptor->isHandlerFor($objectClass)) {
                $callbackQueue->insert(new ClosureWrapper($closure), $descriptor->getPriority());
            }
        }

        foreach ($callbackQueue as $callableWrapper) {
            $this->dispatch($message, $callableWrapper, $callback);
            $forwarded = true;
        }

        if (!$forwarded && !($message instanceof DeadMessage)) {
            self::getLogger()->debug(
                "The following message as a DeadMessage has been posted to '{}' message bus: {}",
                array($this->identifier, $message)
            );
            $this->forwardMessage(new DeadMessage($message));
        }
    }

    protected function doDispatch($message, CallableWrapper $callable)
    {
        $this->interceptors->rewind();
        $chain = new DefaultInterceptorChain($message, $this->interceptors, $callable);
        return $chain->proceed();
    }

    protected function dispatch($message, CallableWrapper $callable, MessageCallback $callback = null)
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
        } catch (Exception $e) {
            self::getLogger()->warn(
                "An error occurred in the following message handler through message bus '{}': {}, message is {}!",
                array($this->identifier, $callable, $message),
                $e
            );
            if ($callback !== null) {
                $callback->onFailure($e);
            }
        }
    }
}
