<?php
declare(strict_types=1);

namespace predaddy\messagehandling\configuration;

use predaddy\messagehandling\FunctionDescriptor;
use predaddy\messagehandling\MessageHandlerDescriptor;

/**
 * Merges {@link FunctionDescriptor}s returned by the given {@link MessageHandlerDescriptor}s.
 *
 * @package predaddy\messagehandling\configuration
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class MultipleMessageHandlerDescriptor implements MessageHandlerDescriptor
{
    /**
     * @var MessageHandlerDescriptor[]
     */
    private $handlerDescriptors;

    /**
     * @var FunctionDescriptor[]
     */
    private $functionDescriptors = null;

    /**
     * @param MessageHandlerDescriptor[] $handlerDescriptors
     */
    public function __construct(array $handlerDescriptors)
    {
        $this->handlerDescriptors = $handlerDescriptors;
    }

    /**
     * @return FunctionDescriptor[]
     */
    public function getFunctionDescriptors() : array
    {
        if ($this->functionDescriptors === null) {
            $this->functionDescriptors = [];
            foreach ($this->handlerDescriptors as $handlerDescriptor) {
                foreach ($handlerDescriptor->getFunctionDescriptors() as $innerFuncDescriptor) {
                    $this->addIfDoesNotContain($innerFuncDescriptor);
                }
            }
        }
        return $this->functionDescriptors;
    }

    private function addIfDoesNotContain(FunctionDescriptor $functionDescriptor) : void
    {
        foreach ($this->functionDescriptors as $currentDescriptor) {
            if ($currentDescriptor->equals($functionDescriptor)) {
                return;
            }
        }
        $this->functionDescriptors[] = $functionDescriptor;
    }
}
