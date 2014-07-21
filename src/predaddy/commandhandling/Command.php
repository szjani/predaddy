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

namespace predaddy\commandhandling;

use predaddy\domain\StateHashAware;
use predaddy\messagehandling\Message;

/**
 * Base interface for all commands in the application.
 * All classes that represent a command should implement this interface.
 *
 * The aggregate id is intended to store the identifier of the aggregate
 * which need to be loaded from a persistent store. If aggregate id is null,
 * a new aggregate instance need to be created.
 *
 * The state hash can solve the lost update problem. If the state hash is null,
 * there will not be state hash check.
 *
 * @see http://www.w3.org/1999/04/Editing/
 * @author Szurovecz János <szjani@szjani.hu>
 */
interface Command extends Message, StateHashAware
{
    /**
     * @return string|null null if it is a create command
     */
    public function aggregateId();
}
