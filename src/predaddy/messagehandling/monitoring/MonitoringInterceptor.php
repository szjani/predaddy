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

namespace predaddy\messagehandling\monitoring;

use Exception;
use precore\lang\Object;
use predaddy\messagehandling\HandlerInterceptor;
use predaddy\messagehandling\InterceptorChain;
use predaddy\messagehandling\MessageBus;

/**
 * Although you can use MessageCallbacks to handle Exceptions, if you need to handle errors in most general way
 * you can use MonitoringInterceptor. It creates an ErrorMessage from the original message
 * and the exception thrown by the handler and posts it to a MessageBus.
 *
 * @package predaddy\messagehandling\monitoring
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class MonitoringInterceptor extends Object implements HandlerInterceptor
{
    /**
     * @var MessageBus
     */
    private $errorBus;

    /**
     * @param MessageBus $errorBus
     */
    public function __construct(MessageBus $errorBus)
    {
        $this->errorBus = $errorBus;
    }

    public function invoke($message, InterceptorChain $chain)
    {
        try {
            return $chain->proceed();
        } catch (Exception $exception) {
            $errorMessage = new ErrorMessage($message, $exception);
            $this->errorBus->post($errorMessage);
            throw $exception;
        }
    }
}
