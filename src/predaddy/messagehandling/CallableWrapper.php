<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use precore\lang\ObjectInterface;

interface CallableWrapper extends ObjectInterface
{
    /**
     * @param object $message
     * @return mixed
     */
    public function invoke($message);

    /**
     * @return \ReflectionFunctionAbstract
     */
    public function reflectionFunction() : \ReflectionFunctionAbstract;
}
