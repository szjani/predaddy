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

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Exception;
use precore\lang\Object;

/**
 * Abstract EventBus which find handler methods in the registered event handlers.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
abstract class AbstractEventBus extends Object implements EventBus
{
    private $identifier;
    private $handlerConfigurations = array();
    private $reader = null;

    /**
     * @param string $identifier Used for logging
     */
    public function __construct($identifier, Reader $reader = null)
    {
        $this->identifier = (string) $identifier;
        AnnotationRegistry::registerFile(__DIR__ . '/EventHandlingAnnotations.php');
        if ($reader == null) {
            $reader = new AnnotationReader();
        }
        $this->reader = $reader;
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

    protected function callHandlerMethod(EventHandler $handler, $method, Event $event)
    {
        $handler->$method($event);
    }

    protected function forwardEvent(Event $event)
    {
        $forwarded = false;
        /* @var $config EventHandlerConfiguration */
        foreach ($this->handlerConfigurations as $config) {
            $methods = $config->getHandlerMethodsFor($event);
            foreach ($methods as $method) {
                try {
                    $this->callHandlerMethod($config->getEventHandler(), $method, $event);
                    $forwarded = true;
                } catch (Exception $e) {
                    self::getLogger()->error('An error occured in an event handler method!', null, $e);
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
        $this->handlerConfigurations[$handler->getClassName()] = new EventHandlerConfiguration($handler, $this->reader);
    }

    public function unregister(EventHandler $handler)
    {
        $key = $handler->getClassName();
        if (array_key_exists($key, $this->handlerConfigurations)) {
            unset($this->handlerConfigurations[$key]);
        }
    }
}
