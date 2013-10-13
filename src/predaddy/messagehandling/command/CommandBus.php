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

namespace predaddy\messagehandling\command;

use AppendIterator;
use ArrayIterator;
use EmptyIterator;
use Iterator;
use predaddy\messagehandling\FunctionDescriptorFactory;
use predaddy\messagehandling\interceptors\TransactionInterceptor;
use predaddy\messagehandling\MessageHandlerDescriptorFactory;
use predaddy\messagehandling\SimpleMessageBus;
use trf4php\TransactionManager;

/**
 * A typical command bus has the following behaviours:
 *  - all command handler methods are wrapped by a unique transaction
 *  - the type of the message must be exactly the same as the parameter in the handler method
 *
 * It's highly recommended to use a CommandFunctionDescriptorFactory instance to automatically achieve
 * the second criteria. In that case Messages must implement the Command interface.
 *
 * If you use it with a CommandBus, use the same TransactionManager instance.
 *
 * @package predaddy\messagehandling\command
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class CommandBus extends SimpleMessageBus
{
    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * @param $identifier
     * @param MessageHandlerDescriptorFactory $handlerDescriptorFactory
     * @param FunctionDescriptorFactory $functionDescriptorFactory
     * @param TransactionManager $transactionManager
     */
    public function __construct(
        $identifier,
        MessageHandlerDescriptorFactory $handlerDescriptorFactory,
        FunctionDescriptorFactory $functionDescriptorFactory,
        TransactionManager $transactionManager
    ) {
        parent::__construct($identifier, $handlerDescriptorFactory, $functionDescriptorFactory);
        $this->transactionManager = $transactionManager;
        $this->setInterceptors(new EmptyIterator());
    }

    /**
     * @param Iterator $interceptors
     */
    public function setInterceptors(Iterator $interceptors)
    {
        $extendedInterceptors = new AppendIterator();
        $extendedInterceptors->append(
            new ArrayIterator(
                array(
                    new TransactionInterceptor($this->transactionManager)
                )
            )
        );
        $extendedInterceptors->append($interceptors);
        parent::setInterceptors($extendedInterceptors);
    }
}
