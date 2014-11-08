<?php
/*
 * Copyright (c) 2013 Janos Szurovecz
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
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ArrayCache;
use precore\lang\ObjectClass;
use predaddy\messagehandling\FunctionDescriptor;
use predaddy\messagehandling\FunctionDescriptorFactory;
use predaddy\messagehandling\MessageHandlerDescriptor;
use predaddy\messagehandling\MethodWrapper;
use ReflectionMethod;

/**
 * Finds handler methods which are annotated with Subscribe.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class AnnotatedMessageHandlerDescriptor implements MessageHandlerDescriptor
{
    /**
     * @var Reader
     */
    private static $reader;

    private $handlerClass;
    private $descriptors = null;
    private $handler;

    /**
     * @var FunctionDescriptorFactory
     */
    private $functionDescriptorFactory;

    public static function init()
    {
        self::$reader = new CachedReader(new AnnotationReader(), new ArrayCache());
    }

    /**
     * @return Reader
     */
    public static function getReader()
    {
        return self::$reader;
    }

    /**
     * @param Reader $reader
     */
    public static function setReader(Reader $reader)
    {
        self::$reader = $reader;
    }

    /**
     * @param object $handler
     * @param FunctionDescriptorFactory $functionDescFactory
     */
    public function __construct($handler, FunctionDescriptorFactory $functionDescFactory)
    {
        $this->handlerClass = ObjectClass::forName(get_class($handler));
        $this->handler = $handler;
        $this->functionDescriptorFactory = $functionDescFactory;
    }

    /**
     * @return FunctionDescriptor[]
     */
    public function getFunctionDescriptors()
    {
        if ($this->descriptors === null) {
            $this->descriptors = $this->findHandlerMethods();
        }
        return $this->descriptors;
    }

    /**
     * @return FunctionDescriptor[]
     */
    protected function findHandlerMethods()
    {
        $result = [];
        /* @var $reflMethod ReflectionMethod */
        foreach ($this->handlerClass->getMethods($this->methodVisibility()) as $reflMethod) {
            $methodAnnotation = self::getReader()->getMethodAnnotation($reflMethod, __NAMESPACE__ . '\Subscribe');
            if ($methodAnnotation === null) {
                continue;
            }
            $funcDescriptor = $this->functionDescriptorFactory->create(
                new MethodWrapper($this->handler, $reflMethod),
                $methodAnnotation->priority
            );
            if (!$funcDescriptor->isValid()) {
                continue;
            }
            $reflMethod->setAccessible(true);
            $result[] = $funcDescriptor;
        }
        return $result;
    }

    protected function methodVisibility()
    {
        return ReflectionMethod::IS_PUBLIC;
    }
}
AnnotationRegistry::registerFile(__DIR__ . '/MessageHandlingAnnotations.php');
AnnotatedMessageHandlerDescriptor::init();
