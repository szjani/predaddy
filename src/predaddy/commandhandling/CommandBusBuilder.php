<?php
declare(strict_types=1);

namespace predaddy\commandhandling;

use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\MessageHandlerDescriptorFactory;
use predaddy\messagehandling\SimpleMessageBusBuilder;

/**
 * Builder for {@link CommandBus}.
 *
 * It's highly recommended to use an {@link CommandFunctionDescriptorFactory} instance.
 * In that case, messages must implement {@link Command} interface.
 *
 * @package predaddy\commandhandling
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class CommandBusBuilder extends SimpleMessageBusBuilder
{
    const DEFAULT_NAME = 'command-bus';

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
            new CommandFunctionDescriptorFactory()
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
     * @return CommandBus
     */
    public function build()
    {
        return new CommandBus($this);
    }
}
CommandBusBuilder::init();
