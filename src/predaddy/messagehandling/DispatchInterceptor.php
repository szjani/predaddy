<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

interface DispatchInterceptor
{
    public function invoke($message, InterceptorChain $chain) : void;
}
