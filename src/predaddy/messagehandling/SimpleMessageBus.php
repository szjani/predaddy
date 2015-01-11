<?php
/*
 * Copyright (c) 2013 Janos Szurovecz
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
use precore\util\Collections;
use precore\util\Objects;
use SplObjectStorage;

/**
 * {@link MessageBus} which find handler methods in the registered message handlers.
 *
 * Handler method finding mechanism can be modified with
 * {@link MessageHandlerDescriptorFactory} and {@link FunctionDescriptorFactory} instances
 * through the builder object.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class SimpleMessageBus extends InterceptableMessageBus implements HandlerFactoryRegisterableMessageBus
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
     * @var SplObjectStorage
     */
    private $functionDescriptors;

    /**
     * @param SimpleMessageBusBuilder $builder
     */
    public function __construct(SimpleMessageBusBuilder $builder = null)
    {
        if ($builder === null) {
            $builder = self::builder();
        }
        parent::__construct($builder->getInterceptors());
        $this->identifier = $builder->getIdentifier();
        $this->exceptionHandler = $builder->getExceptionHandler();
        $this->handlerDescriptorFactory = $builder->getHandlerDescriptorFactory();
        $this->closureDescriptorFactory = $builder->getHandlerDescriptorFactory()->getFunctionDescriptorFactory();
        $this->functionDescriptors = new SplObjectStorage();
        $this->factories = new SplObjectStorage();
    }

    /**
     * @return SimpleMessageBusBuilder
     */
    public static function builder()
    {
        return new SimpleMessageBusBuilder();
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
        foreach ($descriptor->getFunctionDescriptors() as $functionDescriptor) {
            $this->functionDescriptors->attach($functionDescriptor);
        }
    }

    /**
     * @param object $handler
     */
    public function unregister($handler)
    {
        $descriptor = $this->handlerDescriptorFactory->create($handler);
        foreach ($descriptor->getFunctionDescriptors() as $functionDescriptor) {
            $this->functionDescriptors->detach($functionDescriptor);
        }
    }

    /**
     * @param Closure $closure
     * @param int $priority
     */
    public function registerClosure(Closure $closure, $priority = self::DEFAULT_PRIORITY)
    {
        $descriptor = $this->closureDescriptorFactory->create(new ClosureWrapper($closure), $priority);
        $this->functionDescriptors->attach($descriptor);
    }

    /**
     * @param Closure $closure
     * @param int $priority
     */
    public function unregisterClosure(Closure $closure, $priority = self::DEFAULT_PRIORITY)
    {
        $descriptor = $this->closureDescriptorFactory->create(new ClosureWrapper($closure), $priority);
        $this->functionDescriptors->detach($descriptor);
        foreach ($this->functionDescriptors as $key => $value) {
            if ($value->equals($descriptor)) {
                $this->functionDescriptors->offsetUnset($value);
                break;
            }
        }
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
     * @return ArrayObject
     */
    protected function callableWrappersFor($message)
    {
        $objectClass = ObjectClass::forName(get_class($message));
        $heap = Collections::createHeap(Collections::reverseOrder());

        /* @var $functionDescriptor FunctionDescriptor */
        foreach ($this->functionDescriptors as $functionDescriptor) {
            if ($functionDescriptor->isHandlerFor($objectClass)) {
                $heap->insert($functionDescriptor);
            }
        }

        foreach ($this->factories as $factory) {
            /* @var $factoryDescriptor FunctionDescriptor */
            $factoryDescriptor = $this->factories[$factory];
            if ($factoryDescriptor->isHandlerFor($objectClass)) {
                $handler = call_user_func($factory, $message);
                $descriptor = $this->handlerDescriptorFactory->create($handler);
                foreach ($descriptor->getFunctionDescriptors() as $functionDescriptor) {
                    $heap->insert($functionDescriptor);
                }
            }
        }

        $res = new ArrayObject();
        foreach ($heap as $functionDescriptor) {
            $res->append($functionDescriptor->getCallableWrapper());
        }
        return $res;
    }

    public function toString()
    {
        return Objects::toStringHelper($this)
            ->add($this->identifier)
            ->toString();
    }
}
