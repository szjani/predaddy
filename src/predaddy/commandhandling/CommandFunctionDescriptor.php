<?php
declare(strict_types=1);

namespace predaddy\commandhandling;

use predaddy\messagehandling\DefaultFunctionDescriptor;

final class CommandFunctionDescriptor extends DefaultFunctionDescriptor
{
    protected function getBaseMessageClassName() : string
    {
        return __NAMESPACE__ . '\Command';
    }

    protected function canHandleValidMessage($object) : bool
    {
        return get_class($object) === $this->getHandledMessageClassName();
    }
}
