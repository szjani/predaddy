<?php
declare(strict_types=1);

namespace predaddy\messagehandling\util;

use Closure;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class MessageCallbackClosuresBuilder
{
    /**
     * @var Closure|null
     */
    protected $successClosure;

    /**
     * @var Closure|null
     */
    protected $failureClosure;

    /**
     * @param callable $closure
     * @return MessageCallbackClosuresBuilder $this
     */
    public function successClosure(Closure $closure) : self
    {
        $this->successClosure = $closure;
        return $this;
    }

    /**
     * @param callable $closure
     * @return MessageCallbackClosuresBuilder $this
     */
    public function failureClosure(Closure $closure) : self
    {
        $this->failureClosure = $closure;
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getFailureClosure() : ?callable
    {
        return $this->failureClosure;
    }

    /**
     * @return callable|null
     */
    public function getSuccessClosure() : ?callable
    {
        return $this->successClosure;
    }

    /**
     * @return MessageCallbackClosures
     */
    public function build() : MessageCallbackClosures
    {
        return MessageCallbackClosures::build($this);
    }
}
