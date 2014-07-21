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

namespace predaddy\messagehandling;

use Closure;

/**
 * Handler factories can be registered in order to create handlers only when it is required.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
interface HandlerFactoryRegisterableMessageBus extends MessageBus
{
    /**
     * The factory must look like a handler Closure. All messages are being forwarded
     * which is compatible with the defined typehint.
     *
     * The factory must return a handler which the message is being forwarded to
     * just like it would be registered directly to the bus.
     *
     * @param Closure $factory
     * @return mixed
     */
    public function registerHandlerFactory(Closure $factory);

    /**
     * Unregisters the given factory.
     *
     * @param Closure $factory
     * @return mixed
     */
    public function unregisterHandlerFactory(Closure $factory);
}
