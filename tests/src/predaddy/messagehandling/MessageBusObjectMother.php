<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

/**
 * @package predaddy\messagehandling
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class MessageBusObjectMother
{
    /**
     * @param array $interceptors
     * @return SimpleMessageBus
     */
    public static function createAnnotatedBus(array $interceptors = [])
    {
        return SimpleMessageBus::builder()
            ->withInterceptors($interceptors)
            ->build();
    }
}
