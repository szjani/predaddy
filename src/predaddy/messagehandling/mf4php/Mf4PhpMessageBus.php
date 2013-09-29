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

namespace predaddy\messagehandling\mf4php;

use mf4php\DefaultQueue;
use mf4php\Message as Mf4phpMessage;
use mf4php\MessageDispatcher;
use mf4php\MessageListener;
use predaddy\messagehandling\FunctionDescriptorFactory;
use predaddy\messagehandling\Message;
use predaddy\messagehandling\MessageHandlerDescriptorFactory;
use predaddy\messagehandling\SimpleMessageBus;

/**
 * MessageBus implementation which uses mf4php to forward messages.
 *
 * If you use a proper MessageDispatcher it is possible to
 * handle messages after the transaction has been committed.
 *
 * With an asynchronous MessageDispatcher implementation message
 * handling can be asynchronous.
 *
 * Do not register it to the given MessageDispatcher,
 * it is handled by default.
 *
 * Does not support MessageCallback!
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class Mf4PhpMessageBus extends SimpleMessageBus implements MessageListener
{
    private $dispatcher;
    private $queue;
    private $objectMessageFactories = array();
    private $defaultObjectMessageFactory;

    public function __construct(
        $busId,
        MessageHandlerDescriptorFactory $handlerDescriptorFactory,
        FunctionDescriptorFactory $functionDescriptorFactory,
        MessageDispatcher $dispatcher
    ) {
        parent::__construct($busId, $handlerDescriptorFactory, $functionDescriptorFactory);
        $this->dispatcher = $dispatcher;
        $this->queue = new DefaultQueue($busId);
        $this->defaultObjectMessageFactory = new DefaultObjectMessageFactory();
        $dispatcher->addListener($this->queue, $this);
    }

    /**
     * Only full matching class names are used, you should not register
     * a factory for an abstract message class/interface!
     *
     * If there is no registered factory for a particular message class,
     * DefaultObjectMessageFactory will be used.
     *
     * @param ObjectMessageFactory $factory
     * @param $messageClass
     */
    public function registerObjectMessageFactory(ObjectMessageFactory $factory, $messageClass)
    {
        $this->objectMessageFactories[$messageClass] = $factory;
    }

    /**
     * Forward incoming message to handlers.
     *
     * @param Mf4phpMessage $message
     */
    public function onMessage(Mf4phpMessage $message)
    {
        $this->forwardMessage($message->getObject());
    }

    /**
     * Finds the appropriate message factory for the given message.
     *
     * @param Message $message
     * @return ObjectMessageFactory
     */
    protected function findObjectMessageFactory(Message $message)
    {
        $messageClass = $message->getClassName();
        foreach ($this->objectMessageFactories as $class => $factory) {
            if ($class === $messageClass) {
                return $factory;
            }
        }
        return $this->defaultObjectMessageFactory;
    }

    /**
     * Send the message to the message queue.
     *
     * @param Message $message
     */
    protected function innerPost(Message $message)
    {
        $mf4phpMessage = $this->findObjectMessageFactory($message)->createMessage($message);
        $this->dispatcher->send($this->queue, $mf4phpMessage);
    }
}
