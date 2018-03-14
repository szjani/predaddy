<?php
declare(strict_types=1);

namespace predaddy\eventhandling;

use predaddy\messagehandling\DefaultFunctionDescriptor;

final class EventFunctionDescriptor extends DefaultFunctionDescriptor
{
    protected function getBaseMessageClassName() : string
    {
        return __NAMESPACE__ . '\Event';
    }
}
