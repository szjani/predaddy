<?php
declare(strict_types=1);

namespace predaddy\messagehandling\configuration;

use predaddy\messagehandling\FunctionDescriptor;
use predaddy\messagehandling\FunctionDescriptorFactory;
use predaddy\messagehandling\MessageHandlerDescriptor;
use predaddy\messagehandling\MethodWrapper;
use ReflectionMethod;

/**
 * Class ConfiguredMessageHandlerDescriptor
 *
 * @package predaddy\messagehandling\configuration
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class ConfiguredMessageHandlerDescriptor implements MessageHandlerDescriptor
{
    /**
     * @var FunctionDescriptor[]
     */
    private $descriptors = [];

    /**
     * @param object $handler
     * @param FunctionDescriptorFactory $functionDescFactory
     * @param MethodConfiguration[] $methodConfigurations
     * @return ConfiguredMessageHandlerDescriptor
     */
    public static function create($handler, FunctionDescriptorFactory $functionDescFactory, array $methodConfigurations) : ConfiguredMessageHandlerDescriptor
    {
        $descriptors = [];
        foreach ($methodConfigurations as $methodConfiguration) {
            $reflMethod = new ReflectionMethod($handler, $methodConfiguration->getName());
            $funcDescriptor = $functionDescFactory->create(
                new MethodWrapper($handler, $reflMethod),
                $methodConfiguration->getPriority()
            );
            if ($funcDescriptor->isValid()) {
                $descriptors[] = $funcDescriptor;
            }
        }
        return new self($descriptors);
    }

    /**
     * @param FunctionDescriptor[] $descriptors
     */
    private function __construct(array $descriptors)
    {
        $this->descriptors = $descriptors;
    }

    /**
     * @return FunctionDescriptor[]
     */
    public function getFunctionDescriptors() : array
    {
        return $this->descriptors;
    }
}
