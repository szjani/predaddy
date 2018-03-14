<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use Closure;

/**
 * Handler factories can be registered in order to create handlers only when it is required.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface HandlerFactoryRegisterableMessageBus extends MessageBus
{
    /**
     * The factory must look like a handler Closure. All messages are being forwarded
     * which is compatible with the defined typehint.
     *
     * The factory must return a handler which the message is being forwarded to
     * just like it would be registered directly to the bus.
     *
     * @param Closure $factory
     * @return mixed
     */
    public function registerHandlerFactory(Closure $factory);

    /**
     * Unregisters the given factory.
     *
     * @param Closure $factory
     * @return mixed
     */
    public function unregisterHandlerFactory(Closure $factory);
}
