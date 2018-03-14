<?php
declare(strict_types=1);

namespace predaddy\messagehandling\annotation;

use predaddy\messagehandling\CachedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\MessageHandlerDescriptor;

/**
 * Uses Doctrine annotation reader and creates AnnotatedMessageHandlerDescriptor object for each handlers.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class AnnotatedMessageHandlerDescriptorFactory extends CachedMessageHandlerDescriptorFactory
{
    /**
     * @param object $handler
     * @return MessageHandlerDescriptor
     */
    protected function innerCreate($handler) : MessageHandlerDescriptor
    {
        return new AnnotatedMessageHandlerDescriptor($handler, $this->getFunctionDescriptorFactory());
    }
}
