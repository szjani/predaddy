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

namespace predaddy\messagehandling;

use predaddy\messagehandling\annotation\Subscribe;

/**
 * Description of AllMessageHandler
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class AllMessageHandler extends AbstractMessageHandler
{
    /**
     * @Subscribe
     * @param Message $message
     */
    public function handle(Message $message)
    {
        $this->lastMessage = $message;
    }

    /**
     * @Subscribe
     * @param Message $message
     * @param null $param
     */
    public function invalidHandleWithParameter(Message $message, $param = null)
    {
        $this->lastMessage = $message;
    }

    /**
     * @Subscribe
     * @param $message
     */
    public function invalidHandleWithNoMessage($message)
    {
        $this->lastMessage = $message;
    }

    /**
     * @Subscribe
     * @param Message $message
     */
    private function privateHandler(Message $message)
    {
        $this->lastMessage = $message;
    }

    /**
     * @Subscribe
     * @param Message $message
     */
    private function protectedHandler(Message $message)
    {
        $this->lastMessage = $message;
    }
}
