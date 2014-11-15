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

namespace predaddy\messagehandling\util;

use Exception;
use predaddy\messagehandling\MessageCallback;

/**
 * Simple {@link MessageCallback} which holds all hold the last result and exception.
 * Always overwrites the existing values!
 *
 * @package predaddy\messagehandling\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class SimpleMessageCallback implements MessageCallback
{
    /**
     * @var mixed|null
     */
    private $result;

    /**
     * @var Exception|null
     */
    private $exception;

    /**
     * @param mixed $result
     * @return void
     */
    public function onSuccess($result)
    {
        $this->result = $result;
    }

    /**
     * @param Exception $exception
     * @return void
     */
    public function onFailure(Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return mixed|null
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return Exception|null
     */
    public function getException()
    {
        return $this->exception;
    }
}
