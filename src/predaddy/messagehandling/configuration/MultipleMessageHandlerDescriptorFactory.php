<?php
declare(strict_types=1);

namespace predaddy\messagehandling\configuration;

use predaddy\messagehandling\CachedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\FunctionDescriptorFactory;
use predaddy\messagehandling\MessageHandlerDescriptor;
use predaddy\messagehandling\MessageHandlerDescriptorFactory;

/**
 * Handles multiple {@link MessageHandlerDescriptorFactory} objects.
 * Useful if you want to combine handler method scanning/defining strategies.
 *
 * @package predaddy\messagehandling\configuration
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class MultipleMessageHandlerDescriptorFactory extends CachedMessageHandlerDescriptorFactory
{
    /**
     * @var MessageHandlerDescriptorFactory[]
     */
    private $descriptorFactories;

    /**
     * @param FunctionDescriptorFactory $functionDescFactory
     * @param MessageHandlerDescriptorFactory[] $descriptorFactories
     */
    public function __construct(FunctionDescriptorFactory $functionDescFactory, array $descriptorFactories)
    {
        parent::__construct($functionDescFactory);
        $this->descriptorFactories = $descriptorFactories;
    }

    /**
     * @param object $handler
     * @return MessageHandlerDescriptor
     */
    protected function innerCreate($handler) : MessageHandlerDescriptor
    {
        $handlerDescriptors = [];
        foreach ($this->descriptorFactories as $factory) {
            $handlerDescriptors[] = $factory->create($handler);
        }
        return new MultipleMessageHandlerDescriptor($handlerDescriptors);
    }
}
