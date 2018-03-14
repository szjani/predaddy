<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use Exception;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class NullSubscriberExceptionHandler implements SubscriberExceptionHandler
{
    public function handleException(Exception $exception, SubscriberExceptionContext $context) : void
    {
        // noop
    }
}
