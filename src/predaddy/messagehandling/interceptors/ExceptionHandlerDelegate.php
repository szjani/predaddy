<?php

namespace predaddy\messagehandling\interceptors;

use Exception;
use predaddy\messagehandling\SubscriberExceptionContext;
use predaddy\messagehandling\SubscriberExceptionHandler;

/**
 * Delegates all incoming errors to the registered handlers.
 *
 * @package predaddy\messagehandling\interceptors
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class ExceptionHandlerDelegate implements SubscriberExceptionHandler
{
    /**
     * @var SubscriberExceptionHandler[]
     */
    private $handlers;

    /**
     * @param SubscriberExceptionHandler[] $handlers
     */
    public function __construct(array $handlers = [])
    {
        $this->handlers = $handlers;
    }

    public function handleException(Exception $exception, SubscriberExceptionContext $context)
    {
        foreach ($this->handlers as $handler) {
            $handler->handleException($exception, $context);
        }
    }
}
