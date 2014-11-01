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

namespace predaddy\messagehandling\mf4php;

use mf4php\MessageDispatcher;
use predaddy\messagehandling\SimpleMessageBusBuilder;

/**
 * Builder for {@link Mf4PhpMessageBus}.
 *
 * @package predaddy\messagehandling\mf4php
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class Mf4PhpMessageBusBuilder extends SimpleMessageBusBuilder
{
    const DEFAULT_NAME = 'mf4php-bus';

    /**
     * @var MessageDispatcher
     */
    private $dispatcher;

    /**
     * @param MessageDispatcher $dispatcher
     */
    public function __construct(MessageDispatcher $dispatcher)
    {
        parent::__construct();
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return string
     */
    protected static function defaultName()
    {
        return self::DEFAULT_NAME;
    }

    /**
     * @return MessageDispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @return Mf4PhpMessageBus
     */
    public function build()
    {
        return new Mf4PhpMessageBus($this);
    }
}
