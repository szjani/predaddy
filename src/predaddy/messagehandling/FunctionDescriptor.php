<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use precore\lang\Comparable;
use precore\lang\ObjectInterface;

interface FunctionDescriptor extends Comparable, ObjectInterface
{
    /**
     * @return boolean
     */
    public function isValid() : bool;

    /**
     * @param object $message
     * @return boolean
     */
    public function isHandlerFor($message) : bool;

    /**
     * @return CallableWrapper
     */
    public function getCallableWrapper() : CallableWrapper;

    /**
     * @return string
     */
    public function getHandledMessageClassName() : string;

    /**
     * @return int
     */
    public function getPriority() : int;
}
