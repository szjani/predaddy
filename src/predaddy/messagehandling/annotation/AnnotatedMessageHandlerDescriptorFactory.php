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
use precore\lang\ObjectClass;
use predaddy\messagehandling\CachedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\FunctionDescriptorFactory;

/**
 * Uses Doctrine annotation reader and creates AnnotatedMessageHandlerDescriptor object for each handlers.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class AnnotatedMessageHandlerDescriptorFactory extends CachedMessageHandlerDescriptorFactory
{
    private static $defaultReader;

    /**
     * @var Reader
     */
    private $reader;

    public static function registerAnnotations()
    {
        AnnotationRegistry::registerFile(__DIR__ . '/MessageHandlingAnnotations.php');
    }

    private static function getDefaultReader()
    {
        if (self::$defaultReader === null) {
            self::$defaultReader = new AnnotationReader();
        }
        return self::$defaultReader;
    }

    /**
     * @param FunctionDescriptorFactory $functionDescFactory
     * @param Reader $reader if null, an AnnotationReader instance will be used
     */
    public function __construct(FunctionDescriptorFactory $functionDescFactory, Reader $reader = null)
    {
        self::registerAnnotations();
        if ($reader === null) {
            $reader = self::getDefaultReader();
        }
        $this->reader = $reader;
        parent::__construct($functionDescFactory);
    }

    /**
     * @param string $handlerClassName
     * @return AnnotatedMessageHandlerDescriptor
     */
    protected function innerCreate($handlerClassName)
    {
        return new AnnotatedMessageHandlerDescriptor(
            ObjectClass::forName($handlerClassName),
            $this->reader,
            $this->getFunctionDescriptorFactory()
        );
    }

    /**
     * @return Reader
     */
    public function getReader()
    {
        return $this->reader;
    }
}
