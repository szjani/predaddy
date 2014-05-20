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

use LazyMap\CallbackLazyMap;

/**
 * Caches the MessageHandlerDescriptor instances by the class of handlers.
 *
 * @package predaddy\messagehandling
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
abstract class CachedMessageHandlerDescriptorFactory implements MessageHandlerDescriptorFactory
{
    /**
     * @var FunctionDescriptorFactory
     */
    private $functionDescFactory;

    /**
     * @var CallbackLazyMap
     */
    private $descriptorMap;

    /**
     * @param FunctionDescriptorFactory $functionDescFactory
     */
    public function __construct(FunctionDescriptorFactory $functionDescFactory)
    {
        $this->functionDescFactory = $functionDescFactory;
        $this->descriptorMap = new CallbackLazyMap(
            function ($handlerClassName) {
                return $this->innerCreate($handlerClassName);
            }
        );
    }

    /**
     * @param string $handlerClassName
     * @return MessageHandlerDescriptor
     */
    abstract protected function innerCreate($handlerClassName);

    /**
     * @param object $handler
     * @return MessageHandlerDescriptor
     */
    public function create($handler)
    {
        $className = get_class($handler);
        return $this->descriptorMap->$className;
    }

    /**
     * @return FunctionDescriptorFactory
     */
    public function getFunctionDescriptorFactory()
    {
        return $this->functionDescFactory;
    }
}
