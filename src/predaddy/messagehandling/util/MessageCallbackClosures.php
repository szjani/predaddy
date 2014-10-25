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

namespace predaddy\messagehandling\util;

use Closure;
use Exception;
use precore\lang\Object;
use predaddy\messagehandling\MessageCallback;

/**
 * Simple MessageCallback implementation which forwards message handler return values and thrown Exceptions
 * to the appropriate Closure if exists.
 *
 * You can build an instance with the builder() method which provides a builder
 * in order to be able to define optional callbacks.
 *
 * $messageCallback = MessageCallbackClosures::builder()
 *     ->successClosure($callback1)
 *     ->failureClosure($callback2)
 *     ->build();
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class MessageCallbackClosures extends Object implements MessageCallback
{
    /**
     * @var Closure|null
     */
    private $onSuccessClosure;

    /**
     * @var Closure|null
     */
    private $onFailureClosure;

    /**
     * Use builder() method instead.
     */
    private function __construct()
    {
    }

    /**
     * @return MessageCallbackClosuresBuilder
     */
    public static function builder()
    {
        return new MessageCallbackClosuresBuilder();
    }

    /**
     * @param MessageCallbackClosuresBuilder $builder
     * @return MessageCallbackClosures
     */
    public static function build(MessageCallbackClosuresBuilder $builder)
    {
        $callback = new MessageCallbackClosures();
        $callback->onSuccessClosure = $builder->getSuccessClosure();
        $callback->onFailureClosure = $builder->getFailureClosure();
        return $callback;
    }

    public function onSuccess($result)
    {
        if ($this->onSuccessClosure !== null) {
            call_user_func($this->onSuccessClosure, $result);
        }
    }

    public function onFailure(Exception $exception)
    {
        if ($this->onFailureClosure !== null) {
            call_user_func($this->onFailureClosure, $exception);
        }
    }
}
