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

use Closure;
use Exception;
use precore\lang\Object;
use ReflectionFunction;
use SplObjectStorage;

/**
 * Abstract EventBus which find handler methods in the registered event handlers.
 *
 * Handler method finding mechanism can be modified by an EventHandlerDescriptorFactory.
 * If no factory is passed to the constructor, annotation based scanning
 * will be used with  AnnotatedEventHandlerDescriptorFactory.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
abstract class AbstractEventBus extends Object implements EventBus
{
    private $identifier;
    private $handlerDescriptorFactory = null;

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
     * @param $identifier
     * @param EventHandlerDescriptorFactory $handlerDescriptorFactory
     * @param FunctionDescriptorFactory $functionDescriptorFactory
     */
    public function __construct(
        $identifier,
        EventHandlerDescriptorFactory $handlerDescriptorFactory = null,
        FunctionDescriptorFactory $functionDescriptorFactory = null
    ) {
        $this->handlers = new SplObjectStorage();
        $this->closures = new SplObjectStorage();
        $this->identifier = (string) $identifier;

        if ($functionDescriptorFactory === null) {
            $functionDescriptorFactory = new DefaultFunctionDescriptorFactory();
        }
        if ($handlerDescriptorFactory === null) {
            $handlerDescriptorFactory = new AnnotatedEventHandlerDescriptorFactory(null, $functionDescriptorFactory);
        }
        $this->handlerDescriptorFactory = $handlerDescriptorFactory;
        $this->closureDescriptorFactory = $functionDescriptorFactory;
    }

    abstract protected function innerPost(Event $event);

    public function post(Event $event)
    {
        self::getLogger()->info(
            "Event '{}' has been posted to '{}' event bus",
            array($event->getClassName(), $this->identifier)
        );
        $this->innerPost($event);
    }

    protected function forwardEvent(Event $event)
    {
        $forwarded = false;
        $eventReflClass = $event->getObjectClass();
        foreach ($this->handlers as $handler) {
            $descriptor = $this->handlers[$handler];
            $methods = $descriptor->getHandlerMethodsFor($eventReflClass);
            foreach ($methods as $method) {
                try {
                    $method->invoke($handler, $event);
                    $forwarded = true;
                } catch (Exception $e) {
                    self::getLogger()->error('An error occured in an event handler method!', null, $e);
                }
            }
        }
        /* @var $descriptor FunctionDescriptor */
        foreach ($this->closures as $closure) {
            $descriptor = $this->closures[$closure];
            if ($descriptor->isHandlerFor($eventReflClass)) {
                $function = $descriptor->getReflectionFunction();
                try {
                    $function->invoke($event);
                    $forwarded = true;
                } catch (Exception $e) {
                    self::getLogger()->error('An error occured in an event handler closure!', null, $e);
                }
            }
        }
        if (!$forwarded && !($event instanceof DeadEvent)) {
            self::getLogger()->info("DeadEvent has been posted to '{}' event bus", array($this->identifier));
            $this->post(new DeadEvent($event));
        }
    }

    public function register(EventHandler $handler)
    {
        $descriptor = $this->handlerDescriptorFactory->create($handler->getObjectClass());
        $this->handlers->attach($handler, $descriptor);
    }

    public function unregister(EventHandler $handler)
    {
        $this->handlers->detach($handler);
    }

    public function registerClosure(Closure $closure)
    {
        $descriptor = $this->closureDescriptorFactory->create(new ReflectionFunction($closure));
        $this->closures->attach($closure, $descriptor);
    }

    public function unregisterClosure(Closure $closure)
    {
        $this->closures->detach($closure);
    }
}
