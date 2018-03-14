<?php
declare(strict_types=1);

namespace predaddy\domain\eventsourcing;

use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\MessageHandlerDescriptor;

/**
 * Description of EventSourcingEventHandlerDescriptorFactory
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class EventSourcingEventHandlerDescriptorFactory extends AnnotatedMessageHandlerDescriptorFactory
{
    /**
     * @param object $handler
     * @return EventSourcingEventHandlerDescriptor
     */
    protected function innerCreate($handler) : MessageHandlerDescriptor
    {
        return new EventSourcingEventHandlerDescriptor($handler, $this->getFunctionDescriptorFactory());
    }
}
