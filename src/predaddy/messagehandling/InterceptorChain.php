<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

interface InterceptorChain
{
    public function proceed() : void;
}
