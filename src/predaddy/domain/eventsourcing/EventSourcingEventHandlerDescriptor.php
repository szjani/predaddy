<?php
declare(strict_types=1);

namespace predaddy\domain\eventsourcing;

use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptor;
use ReflectionMethod;

/**
 * Handler methods must be private or protected in event sourced aggregate roots.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class EventSourcingEventHandlerDescriptor extends AnnotatedMessageHandlerDescriptor
{
    protected function methodVisibility() : int
    {
        return ReflectionMethod::IS_PRIVATE | ReflectionMethod::IS_PROTECTED;
    }
}
