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

namespace predaddy\domain;

use Doctrine\Common\Annotations\Reader;
use LazyMap\CallbackLazyMap;
use precore\lang\ObjectClass;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\FunctionDescriptorFactory;

/**
 * Description of EventSourcingEventHandlerDescriptorFactory
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class EventSourcingEventHandlerDescriptorFactory extends AnnotatedMessageHandlerDescriptorFactory
{
    private $descriptorMap;

    /**
     * @param FunctionDescriptorFactory $functionDescFactory
     * @param Reader $reader if null, an AnnotationReader instance will be used
     */
    public function __construct(FunctionDescriptorFactory $functionDescFactory, Reader $reader = null)
    {
        parent::__construct($functionDescFactory, $reader);
        $reader = $this->getReader();
        $this->descriptorMap = new CallbackLazyMap(
            function ($handlerClassName) use ($reader, $functionDescFactory) {
                return new EventSourcingEventHandlerDescriptor(
                    ObjectClass::forName($handlerClassName),
                    $reader,
                    $functionDescFactory
                );
            }
        );
    }

    public function create($handler)
    {
        $className = get_class($handler);
        return $this->descriptorMap->$className;
    }
}
