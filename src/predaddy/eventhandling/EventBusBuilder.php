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

namespace predaddy\eventhandling;

use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\SimpleMessageBusBuilder;

/**
 * Builder for {@link EventBus}.
 *
 * It's highly recommended to use an {@link EventFunctionDescriptorFactory} instance.
 * In that case, messages must implement {@link Event} interface.
 *
 * @package predaddy\eventhandling
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class EventBusBuilder extends SimpleMessageBusBuilder
{
    const DEFAULT_NAME = 'event-bus';

    /**
     * @var AnnotatedMessageHandlerDescriptorFactory
     */
    private static $defaultHandlerDescFactory;

    /**
     * Should not be called!
     */
    public static function init()
    {
        self::$defaultHandlerDescFactory = new AnnotatedMessageHandlerDescriptorFactory(
            new EventFunctionDescriptorFactory()
        );
    }

    /**
     * @return AnnotatedMessageHandlerDescriptorFactory
     */
    protected static function defaultHandlerDescFactory()
    {
        return self::$defaultHandlerDescFactory;
    }

    /**
     * @return string
     */
    protected static function defaultName()
    {
        return self::DEFAULT_NAME;
    }

    /**
     * @return EventBus
     */
    public function build()
    {
        return new EventBus($this);
    }
}
EventBusBuilder::init();
