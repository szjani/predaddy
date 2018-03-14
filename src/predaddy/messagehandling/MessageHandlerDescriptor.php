<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

/**
 * Finds and provide handler methods in the given message.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface MessageHandlerDescriptor
{
    /**
     * @return FunctionDescriptor[]
     */
    public function getFunctionDescriptors() : array;
}
