<?php
declare(strict_types=1);

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

    public static function init() : void
    {
        self::$reader = new CachedReader(new AnnotationReader(), new ArrayCache());
    }

    /**
     * @return Reader
     */
    public static function getReader() : Reader
    {
        return self::$reader;
    }

    /**
     * @param Reader $reader
     */
    public static function setReader(Reader $reader) : void
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
    public function getFunctionDescriptors() : array
    {
        if ($this->descriptors === null) {
            $this->descriptors = $this->findHandlerMethods();
        }
        return $this->descriptors;
    }

    /**
     * @return FunctionDescriptor[]
     */
    protected function findHandlerMethods() : array
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

    protected function methodVisibility() : int
    {
        return ReflectionMethod::IS_PUBLIC;
    }
}
AnnotationRegistry::registerFile(__DIR__ . '/MessageHandlingAnnotations.php');
AnnotatedMessageHandlerDescriptor::init();
