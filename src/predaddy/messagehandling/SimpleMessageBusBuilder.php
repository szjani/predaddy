<?php
/*
 * Copyright (c) 2014 Janos Szurovecz
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

use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;

/**
 * Builder for {@link SimpleMessageBus}.
 *
 * @package predaddy\messagehandling
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class SimpleMessageBusBuilder
{
    const DEFAULT_NAME = 'message-bus';

    /**
     * @var AnnotatedMessageHandlerDescriptorFactory
     */
    private static $defaultHandlerDescFactory;

    /**
     * @var NullSubscriberExceptionHandler
     */
    private static $defaultExceptionHandler;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var DispatchInterceptor[]
     */
    private $interceptors = [];

    /**
     * @var SubscriberExceptionHandler
     */
    private $exceptionHandler;

    /**
     * @var MessageHandlerDescriptorFactory
     */
    private $handlerDescriptorFactory;

    public function __construct()
    {
        $this->exceptionHandler = static::defaultExceptionHandler();
        $this->handlerDescriptorFactory = static::defaultHandlerDescFactory();
        $this->identifier = static::defaultName();
    }

    /**
     * Should not be called!
     */
    public static function init()
    {
        self::$defaultHandlerDescFactory = new AnnotatedMessageHandlerDescriptorFactory(
            new DefaultFunctionDescriptorFactory()
        );
        self::$defaultExceptionHandler = new NullSubscriberExceptionHandler();
    }

    /**
     * Override if the default name should be modified.
     *
     * @return string
     */
    protected static function defaultName()
    {
        return self::DEFAULT_NAME;
    }

    /**
     * Override if the default factory should be modified.
     *
     * @return AnnotatedMessageHandlerDescriptorFactory
     */
    protected static function defaultHandlerDescFactory()
    {
        return self::$defaultHandlerDescFactory;
    }

    /**
     * Override if the default exception handler should be modified.
     *
     * @return NullSubscriberExceptionHandler
     */
    protected static function defaultExceptionHandler()
    {
        return self::$defaultExceptionHandler;
    }

    /**
     * @return SimpleMessageBus
     */
    public function build()
    {
        return new SimpleMessageBus($this);
    }

    /**
     * @param $identifier
     * @return $this
     */
    public function withIdentifier($identifier)
    {
        $this->identifier = (string) $identifier;
        return $this;
    }

    /**
     * @param DispatchInterceptor[] $interceptors
     * @return $this
     */
    public function withInterceptors(array $interceptors)
    {
        $this->interceptors = $interceptors;
        return $this;
    }

    /**
     * @param SubscriberExceptionHandler $exceptionHandler
     * @return $this
     */
    public function withExceptionHandler(SubscriberExceptionHandler $exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;
        return $this;
    }

    /**
     * @param MessageHandlerDescriptorFactory $descriptorFactory
     * @return $this
     */
    public function withHandlerDescriptorFactory(MessageHandlerDescriptorFactory $descriptorFactory)
    {
        $this->handlerDescriptorFactory = $descriptorFactory;
        return $this;
    }

    /**
     * @return SubscriberExceptionHandler
     */
    public function getExceptionHandler()
    {
        return $this->exceptionHandler;
    }

    /**
     * @return MessageHandlerDescriptorFactory
     */
    public function getHandlerDescriptorFactory()
    {
        return $this->handlerDescriptorFactory;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return DispatchInterceptor[]
     */
    public function getInterceptors()
    {
        return $this->interceptors;
    }
}
SimpleMessageBusBuilder::init();
