<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use Iterator;

final class DefaultInterceptorChain implements InterceptorChain
{
    /**
     * @var object
     */
    private $message;

    /**
     * @var Iterator
     */
    private $handlerInterceptors;

    /**
     * @var callable
     */
    private $dispatcher;

    /**
     * @param $message
     * @param Iterator $handlerInterceptors
     * @param callable $dispatcher
     */
    public function __construct($message, Iterator $handlerInterceptors, callable $dispatcher)
    {
        $this->message = $message;
        $this->handlerInterceptors = $handlerInterceptors;
        $this->dispatcher = $dispatcher;
    }

    public function proceed() : void
    {
        if ($this->handlerInterceptors->valid()) {
            $current = $this->handlerInterceptors->current();
            $this->handlerInterceptors->next();
            $current->invoke($this->message, $this);
            return;
        }
        call_user_func($this->dispatcher);
    }
}
