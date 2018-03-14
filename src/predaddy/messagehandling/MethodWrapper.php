<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use precore\lang\BaseObject;
use precore\lang\ObjectInterface;
use precore\util\Objects;
use ReflectionMethod;

final class MethodWrapper extends BaseObject implements CallableWrapper
{
    /**
     * @var object
     */
    private $object;

    /**
     * @var ReflectionMethod
     */
    private $method;

    public function __construct($object, ReflectionMethod $method)
    {
        $this->object = $object;
        $this->method = $method;
    }

    public function invoke($message)
    {
        return $this->method->invoke($this->object, $message);
    }

    public function toString() : string
    {
        return Objects::toStringHelper($this)
            ->add('objectClass', get_class($this->object))
            ->add('methodName', $this->method->getName())
            ->toString();
    }

    public function equals(ObjectInterface $object = null) : bool
    {
        if ($object === $this) {
            return true;
        }
        return $object instanceof MethodWrapper
            && Objects::equal($this->object, $object->object)
            && Objects::equal($this->method, $object->method);
    }

    /**
     * @return \ReflectionFunctionAbstract
     */
    public function reflectionFunction() : \ReflectionFunctionAbstract
    {
        return $this->method;
    }
}
