<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

/**
 * Factory for creating FunctionDescriptor instances.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class DefaultFunctionDescriptorFactory implements FunctionDescriptorFactory
{
    /**
     * @param CallableWrapper $callableWrapper
     * @param int $priority
     * @return DefaultFunctionDescriptor
     */
    public function create(CallableWrapper $callableWrapper, int $priority) : FunctionDescriptor
    {
        return new DefaultFunctionDescriptor($callableWrapper, $priority);
    }
}
