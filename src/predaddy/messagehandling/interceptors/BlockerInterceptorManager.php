<?php
declare(strict_types=1);

namespace predaddy\messagehandling\interceptors;

use Exception;
use precore\lang\BaseObject;
use predaddy\messagehandling\DispatchInterceptor;
use predaddy\messagehandling\InterceptorChain;
use predaddy\messagehandling\SubscriberExceptionContext;
use predaddy\messagehandling\SubscriberExceptionHandler;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class BlockerInterceptorManager extends BaseObject implements DispatchInterceptor, SubscriberExceptionHandler
{
    /**
     * @var BlockerInterceptor
     */
    private $blockerInterceptor;

    public function __construct(BlockerInterceptor $blockerInterceptor)
    {
        $this->blockerInterceptor = $blockerInterceptor;
    }

    public function invoke($message, InterceptorChain $chain) : void
    {
        $this->blockerInterceptor->startBlocking();
        $chain->proceed();
        $this->blockerInterceptor->stopBlocking();
        $this->blockerInterceptor->flush();
    }

    public function handleException(Exception $exception, SubscriberExceptionContext $context) : void
    {
        $this->blockerInterceptor->clear();
        self::getLogger()->debug(
            "Blocked chains have been cleared due to thrown exception with context '{}'!",
            [$context],
            $exception
        );
    }
}
