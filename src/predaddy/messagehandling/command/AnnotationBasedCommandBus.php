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
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\interceptors\TransactionInterceptor;
use predaddy\messagehandling\MessageHandlerDescriptorFactory;
use predaddy\messagehandling\SimpleMessageBus;
use trf4php\TransactionManager;

class AnnotationBasedCommandBus extends SimpleMessageBus
{
    private static $defaultFunctionDescriptorFactory;
    private static $defaultMessageHandlerDescriptorFactory;

    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * @return CommandFunctionDescriptorFactory
     */
    public static function getDefaultFunctionDescriptorFactory()
    {
        if (self::$defaultFunctionDescriptorFactory === null) {
            self::$defaultFunctionDescriptorFactory = new CommandFunctionDescriptorFactory();
        }
        return self::$defaultFunctionDescriptorFactory;
    }

    /**
     * @return MessageHandlerDescriptorFactory
     */
    public static function getDefaultMessageHandlerDescriptorFactory()
    {
        if (self::$defaultMessageHandlerDescriptorFactory === null) {
            self::$defaultMessageHandlerDescriptorFactory = new AnnotatedMessageHandlerDescriptorFactory(
                null,
                self::getDefaultFunctionDescriptorFactory()
            );
        }
        return self::$defaultMessageHandlerDescriptorFactory;
    }

    /**
     * @param $identifier
     * @param TransactionManager $transactionManager
     * @param AnnotatedMessageHandlerDescriptorFactory $handlerDescriptorFactory
     */
    public function __construct(
        $identifier,
        TransactionManager $transactionManager,
        AnnotatedMessageHandlerDescriptorFactory $handlerDescriptorFactory = null
    ) {
        if ($handlerDescriptorFactory === null) {
            $handlerDescriptorFactory = self::getDefaultMessageHandlerDescriptorFactory();
        }
        parent::__construct(
            $identifier,
            $handlerDescriptorFactory,
            self::getDefaultFunctionDescriptorFactory()
        );
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
