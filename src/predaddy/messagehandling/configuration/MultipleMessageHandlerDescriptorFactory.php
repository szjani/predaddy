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

namespace predaddy\messagehandling\configuration;

use predaddy\messagehandling\CachedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\FunctionDescriptorFactory;
use predaddy\messagehandling\MessageHandlerDescriptor;
use predaddy\messagehandling\MessageHandlerDescriptorFactory;

/**
 * Handles multiple {@link MessageHandlerDescriptorFactory} objects.
 * Useful if you want to combine handler method scanning/defining strategies.
 *
 * @package predaddy\messagehandling\configuration
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class MultipleMessageHandlerDescriptorFactory extends CachedMessageHandlerDescriptorFactory
{
    /**
     * @var MessageHandlerDescriptorFactory[]
     */
    private $descriptorFactories;

    /**
     * @param FunctionDescriptorFactory $functionDescFactory
     * @param MessageHandlerDescriptorFactory[] $descriptorFactories
     */
    public function __construct(FunctionDescriptorFactory $functionDescFactory, array $descriptorFactories)
    {
        parent::__construct($functionDescFactory);
        $this->descriptorFactories = $descriptorFactories;
    }

    /**
     * @param object $handler
     * @return MessageHandlerDescriptor
     */
    protected function innerCreate($handler)
    {
        $handlerDescriptors = [];
        foreach ($this->descriptorFactories as $factory) {
            $handlerDescriptors[] = $factory->create($handler);
        }
        return new MultipleMessageHandlerDescriptor($handlerDescriptors);
    }
}
