<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface MessageHandlerDescriptorFactory
{
    /**
     * @param object $handler
     * @return MessageHandlerDescriptor
     */
    public function create($handler) : MessageHandlerDescriptor;

    /**
     * @return FunctionDescriptorFactory
     */
    public function getFunctionDescriptorFactory() : FunctionDescriptorFactory;
}
