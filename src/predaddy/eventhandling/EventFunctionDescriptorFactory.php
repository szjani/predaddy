<?php
declare(strict_types=1);

namespace predaddy\eventhandling;

use predaddy\messagehandling\CallableWrapper;
use predaddy\messagehandling\FunctionDescriptor;
use predaddy\messagehandling\FunctionDescriptorFactory;

final class EventFunctionDescriptorFactory implements FunctionDescriptorFactory
{
    /**
     * @param CallableWrapper $callableWrapper
     * @param int $priority
     * @return FunctionDescriptor
     */
    public function create(CallableWrapper $callableWrapper, int $priority) : FunctionDescriptor
    {
        return new EventFunctionDescriptor($callableWrapper, $priority);
    }
}
