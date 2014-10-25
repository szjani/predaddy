<?php
/*
 * Copyright (c) 2012-2014 Janos Szurovecz
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

use precore\lang\Object;
use precore\util\Objects;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class SubscriberExceptionContext extends Object
{
    private $messageBus;
    private $message;
    private $callableWrapper;

    public function __construct(MessageBus $messageBus, $message, CallableWrapper $callableWrapper)
    {
        $this->callableWrapper = $callableWrapper;
        $this->message = $message;
        $this->messageBus = $messageBus;
    }

    /**
     * @return CallableWrapper
     */
    public function getCallableWrapper()
    {
        return $this->callableWrapper;
    }

    /**
     * @return object
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return MessageBus
     */
    public function getMessageBus()
    {
        return $this->messageBus;
    }

    public function toString()
    {
        return Objects::toStringHelper($this)
            ->add('bus', $this->messageBus)
            ->add('message', $this->message)
            ->add('wrapper', $this->callableWrapper)
            ->toString();
    }
}
