<?php
declare(strict_types=1);

namespace predaddy\messagehandling\configuration;

use predaddy\messagehandling\CachedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\FunctionDescriptorFactory;
use predaddy\messagehandling\MessageHandlerDescriptor;

/**
 * Creates {@link ConfiguredMessageHandlerDescriptor}s according to the given {@link Configuration}.
 *
 * Can be passed to a {@link SimpleMessageBus} in order to manage handler methods based on the configurations,
 * instead of the default annotation scanning.
 *
 * @package predaddy\messagehandling\configuration
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ConfiguredMessageHandlerDescriptorFactory extends CachedMessageHandlerDescriptorFactory
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param FunctionDescriptorFactory $functionDescFactory
     * @param Configuration $configuration
     */
    public function __construct(FunctionDescriptorFactory $functionDescFactory, Configuration $configuration)
    {
        parent::__construct($functionDescFactory);
        $this->configuration = $configuration;
    }

    /**
     * @param object $handler
     * @return MessageHandlerDescriptor
     */
    protected function innerCreate($handler) : MessageHandlerDescriptor
    {
        return ConfiguredMessageHandlerDescriptor::create(
            $handler,
            $this->getFunctionDescriptorFactory(),
            $this->configuration->methodsFor($handler)
        );
    }
}
