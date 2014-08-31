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

namespace predaddy\commandhandling;

use predaddy\domain\Repository;
use predaddy\messagehandling\MessageBusFactory;
use predaddy\messagehandling\MessageHandlerDescriptorFactory;
use predaddy\messagehandling\SubscriberExceptionHandler;

/**
 * DirectCommandBus automatically registers a DirectCommandForwarder object as a handler
 * which handles all unhandled commands. This bus should be used if business method parameters
 * in the aggregates are Command objects.
 *
 * If you need to handle a particular command explicit, you can register your own command handler.
 * In this case the command is not being dispatched to the registered DirectCommandForwarder object
 * but you have to manage that aggregate instead.
 *
 * If you have specialized repositories for your aggregates, it is recommended to use RepositoryDelegate.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class DirectCommandBus extends CommandBus
{
    /**
     * @param Repository $repository Is being passed to the registered DirectCommandForwarder
     * @param MessageBusFactory $messageBusFactory Is being passed to the registered DirectCommandForwarder
     * @param MessageHandlerDescriptorFactory $handlerDescFactory
     * @param array $interceptors
     * @param SubscriberExceptionHandler $exceptionHandler
     * @param string $identifier
     */
    public function __construct(
        Repository $repository,
        MessageBusFactory $messageBusFactory,
        MessageHandlerDescriptorFactory $handlerDescFactory,
        array $interceptors = [],
        SubscriberExceptionHandler $exceptionHandler = null,
        $identifier = self::DEFAULT_NAME
    ) {
        parent::__construct($handlerDescFactory, $interceptors, $exceptionHandler, $identifier);
        $this->register(new DirectCommandForwarder($repository, $messageBusFactory));
    }
}
