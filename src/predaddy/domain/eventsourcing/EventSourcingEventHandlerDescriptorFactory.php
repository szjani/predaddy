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

namespace predaddy\domain\eventsourcing;

use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;

/**
 * Description of EventSourcingEventHandlerDescriptorFactory
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
final class EventSourcingEventHandlerDescriptorFactory extends AnnotatedMessageHandlerDescriptorFactory
{
    /**
     * @param object $handler
     * @return EventSourcingEventHandlerDescriptor
     */
    protected function innerCreate($handler)
    {
        return new EventSourcingEventHandlerDescriptor(
            $handler,
            $this->getReader(),
            $this->getFunctionDescriptorFactory()
        );
    }
}
