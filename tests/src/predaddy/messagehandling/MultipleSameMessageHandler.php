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
 * Description of MultipleSameMessageHandler
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class MultipleSameMessageHandler extends AbstractMessageHandler
{
    public $lastMessage2;
    public $lastMessage3;
    public $lastMessage4;

    /**
     * @Subscribe
     */
    public function handle1(SimpleMessage $message)
    {
        $this->lastMessage = $message;
    }

    /**
     * @Subscribe
     */
    public function handle2(Message $message)
    {
        $this->lastMessage2 = $message;
    }

    /**
     * @Subscribe
     */
    public function handle3(SimpleMessage $message)
    {
        $this->lastMessage3 = $message;
    }

    /**
     * @Subscribe
     */
    public function handle4(AbstractMessage $message)
    {
        $this->lastMessage4 = $message;
    }
}
