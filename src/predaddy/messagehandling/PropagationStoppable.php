<?php
/*
 * Copyright (c) 2015 Janos Szurovecz
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

/**
 * A message can implement this interface to be able to notify the message bus,
 * that the rest of the message handlers should not be called.
 *
 * Note, that it is not required to be considered by the message bus.
 *
 * @package predaddy\messagehandling
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface PropagationStoppable
{
    /**
     * The rest of the message handlers should not be called by the message bus.
     *
     * @return void
     */
    public function stopPropagation();

    /**
     * Whether the propagation is marked as stopped.
     *
     * @return boolean
     */
    public function isPropagationStopped();
}
