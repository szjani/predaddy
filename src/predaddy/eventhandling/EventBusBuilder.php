<?php
declare(strict_types=1);

namespace predaddy\eventhandling;

use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\MessageHandlerDescriptorFactory;
use predaddy\messagehandling\SimpleMessageBusBuilder;

/**
 * Builder for {@link EventBus}.
 *
 * It's highly recommended to use an {@link EventFunctionDescriptorFactory} instance.
 * In that case, messages must implement {@link Event} interface.
 *
 * @package predaddy\eventhandling
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class EventBusBuilder extends SimpleMessageBusBuilder
{
    const DEFAULT_NAME = 'event-bus';

    /**
     * @var AnnotatedMessageHandlerDescriptorFactory
     */
    private static $defaultHandlerDescFactory;

    /**
     * Should not be called!
     */
    public static function init() : void
    {
        self::$defaultHandlerDescFactory = new AnnotatedMessageHandlerDescriptorFactory(
            new EventFunctionDescriptorFactory()
        );
    }

    /**
     * @return AnnotatedMessageHandlerDescriptorFactory
     */
    protected static function defaultHandlerDescFactory() : MessageHandlerDescriptorFactory
    {
        return self::$defaultHandlerDescFactory;
    }

    /**
     * @return string
     */
    protected static function defaultName() : string
    {
        return self::DEFAULT_NAME;
    }

    /**
     * @return EventBus
     */
    public function build()
    {
        return new EventBus($this);
    }
}
EventBusBuilder::init();
