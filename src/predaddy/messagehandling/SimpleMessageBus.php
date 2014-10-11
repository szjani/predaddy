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

use ArrayObject;
use Closure;
use Exception;
use precore\lang\ObjectClass;
use precore\util\Objects;
use SplObjectStorage;

/**
 * MessageBus which find handler methods in the registered message handlers.
 *
 * Handler method finding mechanism can be modified by the given
 * MessageHandlerDescriptorFactory and FunctionDescriptorFactory instances.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class SimpleMessageBus extends InterceptableMessageBus implements HandlerFactoryRegisterableMessageBus
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
     * @var FunctionDescriptorFactory
     */
    private $closureDescriptorFactory;

    /**
     * @var SplObjectStorage
     */
    private $factories;

    /**
     * @var SubscriberExceptionHandler
     */
    private $exceptionHandler;

    /**
     * @var FunctionDescriptors
     */
    private $functionDescriptors;

    /**
     * @param MessageHandlerDescriptorFactory $handlerDescFactory
     * @param array $interceptors
     * @param SubscriberExceptionHandler $exceptionHandler
     * @param string $identifier
     */
    public function __construct(
        MessageHandlerDescriptorFactory $handlerDescFactory,
        array $interceptors = [],
        SubscriberExceptionHandler $exceptionHandler = null,
        $identifier = self::DEFAULT_NAME
    ) {
        parent::__construct($interceptors);
        $this->factories = new SplObjectStorage();
        $this->identifier = (string) $identifier;
        $this->handlerDescriptorFactory = $handlerDescFactory;
        $this->closureDescriptorFactory = $handlerDescFactory->getFunctionDescriptorFactory();
        if ($exceptionHandler === null) {
            $exceptionHandler = new NullSubscriberExceptionHandler();
        }
        $this->exceptionHandler = $exceptionHandler;
        $this->functionDescriptors = new FunctionDescriptors();
    }

    public function registerHandlerFactory(Closure $factory)
    {
        $descriptor = $this->closureDescriptorFactory->create(new ClosureWrapper($factory), self::DEFAULT_PRIORITY);
        $this->factories->attach($factory, $descriptor);
    }

    public function unregisterHandlerFactory(Closure $factory)
    {
        $this->factories->detach($factory);
    }

    /**
     * @param object $handler
     */
    public function register($handler)
    {
        $descriptor = $this->handlerDescriptorFactory->create($handler);
        foreach ($descriptor->getFunctionDescriptors() as $functionDescriptors) {
            $this->functionDescriptors->add($functionDescriptors);
        }
    }

    /**
     * @param object $handler
     */
    public function unregister($handler)
    {
        $descriptor = $this->handlerDescriptorFactory->create($handler);
        foreach ($descriptor->getFunctionDescriptors() as $functionDescriptors) {
            $this->functionDescriptors->remove($functionDescriptors);
        }
    }

    /**
     * @param Closure $closure
     * @param int $priority
     */
    public function registerClosure(Closure $closure, $priority = self::DEFAULT_PRIORITY)
    {
        $descriptor = $this->closureDescriptorFactory->create(new ClosureWrapper($closure), $priority);
        $this->functionDescriptors->add($descriptor);
    }

    /**
     * @param Closure $closure
     * @param int $priority
     */
    public function unregisterClosure(Closure $closure, $priority = self::DEFAULT_PRIORITY)
    {
        $descriptor = $this->closureDescriptorFactory->create(new ClosureWrapper($closure), $priority);
        $this->functionDescriptors->remove($descriptor);
    }

    /**
     * Dispatches $message to all handlers.
     *
     * @param $message
     * @param MessageCallback $callback
     * @return void
     */
    protected function dispatch($message, MessageCallback $callback)
    {
        $handled = false;
        foreach ($this->callableWrappersFor($message) as $callable) {
            $handled = true;
            try {
                $result = $callable->invoke($message);
                self::getLogger()->debug(
                    "The following message has been dispatched to handler '{}' through message bus '{}': {}",
                    [$callable, $this, $message]
                );
                if ($result !== null) {
                    $callback->onSuccess($result);
                }
            } catch (Exception $exp) {
                self::getLogger()->warn(
                    "An error occurred in the following message handler through message bus '{}': {}, message is {}!",
                    [$this, $callable, $message],
                    $exp
                );
                $context = new SubscriberExceptionContext($this, $message, $callable);
                try {
                    $this->exceptionHandler->handleException($exp, $context);
                } catch (Exception $e) {
                    self::getLogger()->warn(
                        "An error occurred in the exception handler with context '{}'",
                        [$context],
                        $e
                    );
                }
                try {
                    $callback->onFailure($exp);
                } catch (Exception $e) {
                    self::getLogger()->warn("An error occurred in message callback on bus '{}'", [$this], $e);
                }
            }
        }
        if (!$handled && !($message instanceof DeadMessage)) {
            self::getLogger()->debug(
                "The following message as a DeadMessage is being posted to '{}' message bus: {}",
                [$this, $message]
            );
            $this->dispatch(new DeadMessage($message), $callback);
        }
    }

    /**
     * @param $message
     * @return \Traversable|\Countable
     */
    protected function callableWrappersFor($message)
    {
        $objectClass = ObjectClass::forName(get_class($message));
        /* @var $functionDescriptors FunctionDescriptors */
        $functionDescriptors = clone $this->functionDescriptors;

        foreach ($this->factories as $factory) {
            /* @var $factoryDescriptor FunctionDescriptor */
            $factoryDescriptor = $this->factories[$factory];
            if ($factoryDescriptor->isHandlerFor($objectClass)) {
                $handler = call_user_func($factory, $message);
                $descriptor = $this->handlerDescriptorFactory->create($handler);
                foreach ($descriptor->getFunctionDescriptors() as $functionDescriptor) {
                    $functionDescriptors->add($functionDescriptor);
                }
            }
        }

        $res = new ArrayObject();
        /* @var $functionDescriptor FunctionDescriptor */
        foreach ($functionDescriptors as $functionDescriptor) {
            if ($functionDescriptor->isHandlerFor($objectClass)) {
                $res->append($functionDescriptor->getCallableWrapper());
            }
        }
        return $res;
    }

    public function toString()
    {
        return Objects::toStringHelper($this)
            ->add('id', $this->identifier)
            ->toString();
    }
}
