<?php
declare(strict_types=1);

namespace predaddy\messagehandling\interceptors;

use predaddy\messagehandling\InterceptorChain;

/**
 * @package src\predaddy\messagehandling\interceptors
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class InterceptorChainSpy implements InterceptorChain
{
    private $called = 0;

    public function neverCalled()
    {
        return $this->called === 0;
    }

    public function calledTimes($times)
    {
        return $this->called === $times;
    }

    public function proceed() : void
    {
        $this->called++;
    }
}
