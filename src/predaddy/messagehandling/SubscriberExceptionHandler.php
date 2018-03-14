<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use Exception;

/**
 * Can handle exceptions thrown by message handlers.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface SubscriberExceptionHandler
{
    /**
     * Called by {@link SimpleMessageBus} when a handler throws an exception.
     * Should not throw any exceptions, however all exceptions thrown by this method will be caught by the bus.
     *
     * @param Exception $exception thrown by the message handler
     * @param SubscriberExceptionContext $context holds all available information
     * @return void
     */
    public function handleException(Exception $exception, SubscriberExceptionContext $context) : void;
}
