<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use Closure;
use precore\lang\BaseObject;
use precore\lang\ObjectInterface;
use ReflectionFunction;

final class ClosureWrapper extends BaseObject implements CallableWrapper
{
    /**
     * @var Closure
     */
    private $closure;
    private $reflectionFunction;

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
        $this->reflectionFunction = new ReflectionFunction($closure);
    }

    public function invoke($message)
    {
        return call_user_func($this->closure, $message);
    }

    /**
     * @return \ReflectionFunctionAbstract
     */
    public function reflectionFunction() : \ReflectionFunctionAbstract
    {
        return $this->reflectionFunction;
    }

    public function equals(ObjectInterface $object = null) : bool
    {
        if ($object === $this) {
            return true;
        }
        return $object instanceof ClosureWrapper
            && $this->closure === $object->closure;
    }
}
