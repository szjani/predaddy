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

namespace predaddy\commandhandling;

use LogicException;
use predaddy\messagehandling\MessageHandlerDescriptorFactory;
use predaddy\messagehandling\SimpleMessageBus;
use predaddy\messagehandling\SubscriberExceptionHandler;

/**
 * A typical command bus has the following behaviours:
 *  - all command handler methods are wrapped by a unique transaction
 *  - the type of the message must be exactly the same as the parameter in the handler method
 *
 * It's highly recommended to use a CommandFunctionDescriptorFactory instance to automatically achieve
 * the second criteria. In that case Messages must implement the Command interface.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class CommandBus extends SimpleMessageBus
{
    const DEFAULT_NAME = 'command-bus';

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
        parent::__construct(
            $handlerDescFactory,
            $interceptors,
            $exceptionHandler,
            $identifier
        );
    }

    protected function callableWrappersFor($message)
    {
        $wrappers = parent::callableWrappersFor($message);
        if (1 < count($wrappers)) {
            $class = get_class($message);
            throw new LogicException("More than one command handler is registered for message '$class'");
        }
        return $wrappers;
    }
}
