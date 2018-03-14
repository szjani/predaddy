<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use SplObjectStorage;

/**
 * Caches the MessageHandlerDescriptor instances by the class of handlers.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class CachedMessageHandlerDescriptorFactory implements MessageHandlerDescriptorFactory
{
    /**
     * @var FunctionDescriptorFactory
     */
    private $functionDescFactory;

    /**
     * @var SplObjectStorage
     */
    private $descriptorMap;

    /**
     * @param FunctionDescriptorFactory $functionDescFactory
     */
    public function __construct(FunctionDescriptorFactory $functionDescFactory)
    {
        $this->functionDescFactory = $functionDescFactory;
        $this->descriptorMap = new SplObjectStorage();
    }

    /**
     * @param object $handler
     * @return MessageHandlerDescriptor
     */
    abstract protected function innerCreate($handler) : MessageHandlerDescriptor;

    /**
     * @param object $handler
     * @return MessageHandlerDescriptor
     */
    public function create($handler) : MessageHandlerDescriptor
    {
        if (!$this->descriptorMap->contains($handler)) {
            $this->descriptorMap->attach($handler, $this->innerCreate($handler));
        }
        return $this->descriptorMap->offsetGet($handler);
    }

    /**
     * @return FunctionDescriptorFactory
     */
    public function getFunctionDescriptorFactory() : FunctionDescriptorFactory
    {
        return $this->functionDescFactory;
    }
}
