<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

interface FunctionDescriptorFactory
{
    /**
     * @param CallableWrapper $callableWrapper
     * @param int $priority
     * @return FunctionDescriptor
     */
    public function create(CallableWrapper $callableWrapper, int $priority) : FunctionDescriptor;
}
