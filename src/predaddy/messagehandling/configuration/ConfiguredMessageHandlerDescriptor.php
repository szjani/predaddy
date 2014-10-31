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

use predaddy\messagehandling\FunctionDescriptor;
use predaddy\messagehandling\FunctionDescriptorFactory;
use predaddy\messagehandling\MessageHandlerDescriptor;
use predaddy\messagehandling\MethodWrapper;
use ReflectionMethod;

/**
 * Class ConfiguredMessageHandlerDescriptor
 *
 * @package predaddy\messagehandling\configuration
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class ConfiguredMessageHandlerDescriptor implements MessageHandlerDescriptor
{
    /**
     * @var FunctionDescriptor[]
     */
    private $descriptors = [];

    /**
     * @param object $handler
     * @param FunctionDescriptorFactory $functionDescFactory
     * @param MethodConfiguration[] $methodConfigurations
     * @return ConfiguredMessageHandlerDescriptor
     */
    public static function create($handler, FunctionDescriptorFactory $functionDescFactory, array $methodConfigurations)
    {
        $descriptors = [];
        foreach ($methodConfigurations as $methodConfiguration) {
            $reflMethod = new ReflectionMethod($handler, $methodConfiguration->getName());
            $funcDescriptor = $functionDescFactory->create(
                new MethodWrapper($handler, $reflMethod),
                $methodConfiguration->getPriority()
            );
            if ($funcDescriptor->isValid()) {
                $descriptors[] = $funcDescriptor;
            }
        }
        return new self($descriptors);
    }

    /**
     * @param FunctionDescriptor[] $descriptors
     */
    private function __construct(array $descriptors)
    {
        $this->descriptors = $descriptors;
    }

    /**
     * @return FunctionDescriptor[]
     */
    public function getFunctionDescriptors()
    {
        return $this->descriptors;
    }
}
