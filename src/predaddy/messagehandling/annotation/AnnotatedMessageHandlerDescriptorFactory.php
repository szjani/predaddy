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

namespace predaddy\messagehandling\annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use predaddy\messagehandling\DefaultFunctionDescriptorFactory;
use predaddy\messagehandling\FunctionDescriptorFactory;
use predaddy\messagehandling\MessageHandlerDescriptorFactory;
use ReflectionClass;

/**
 * Description of AnnotatedEventHandlerDescriptorFactory
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class AnnotatedMessageHandlerDescriptorFactory implements MessageHandlerDescriptorFactory
{
    private $reader;

    /**
     * @var FunctionDescriptorFactory
     */
    private $functionDescriptorFactory;

    public static function registerAnnotations()
    {
        AnnotationRegistry::registerFile(__DIR__ . '/MessageHandlingAnnotations.php');
    }

    public function __construct(Reader $reader = null, FunctionDescriptorFactory $functionDescriptorFactory = null)
    {
        self::registerAnnotations();
        if ($reader === null) {
            $reader = new AnnotationReader();
        }
        if ($functionDescriptorFactory === null) {
            $functionDescriptorFactory = new DefaultFunctionDescriptorFactory();
        }
        $this->reader = $reader;
        $this->functionDescriptorFactory = $functionDescriptorFactory;
    }

    /**
     * @return Reader
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * @return FunctionDescriptorFactory
     */
    public function getFunctionDescriptorFactory()
    {
        return $this->functionDescriptorFactory;
    }

    public function create($handler)
    {
        return new AnnotatedMessageHandlerDescriptor(new ReflectionClass($handler), $this->reader, $this->functionDescriptorFactory);
    }
}
