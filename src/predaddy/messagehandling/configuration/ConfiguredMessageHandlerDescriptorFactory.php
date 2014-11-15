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

/**
 * Creates {@link ConfiguredMessageHandlerDescriptor}s according to the given {@link Configuration}.
 *
 * Can be passed to a {@link SimpleMessageBus} in order to manage handler methods based on the configurations,
 * instead of the default annotation scanning.
 *
 * @package predaddy\messagehandling\configuration
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ConfiguredMessageHandlerDescriptorFactory extends CachedMessageHandlerDescriptorFactory
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param FunctionDescriptorFactory $functionDescFactory
     * @param Configuration $configuration
     */
    public function __construct(FunctionDescriptorFactory $functionDescFactory, Configuration $configuration)
    {
        parent::__construct($functionDescFactory);
        $this->configuration = $configuration;
    }

    /**
     * @param object $handler
     * @return MessageHandlerDescriptor
     */
    protected function innerCreate($handler)
    {
        return ConfiguredMessageHandlerDescriptor::create(
            $handler,
            $this->getFunctionDescriptorFactory(),
            $this->configuration->methodsFor($handler)
        );
    }
}
