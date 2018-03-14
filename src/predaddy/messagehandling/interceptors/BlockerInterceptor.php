<?php
declare(strict_types=1);

namespace predaddy\messagehandling\interceptors;

use precore\lang\BaseObject;
use predaddy\messagehandling\DispatchInterceptor;
use predaddy\messagehandling\InterceptorChain;
use predaddy\messagehandling\NonBlockable;

/**
 * It can block and buffer messages in blocking mode. Its state can be configured with start and stop methods.
 * The collected messages can be flushed or cleared according to the needs.
 *
 * <p> The control can be done by a manager object, which is also an interceptor.
 * It should be used if two buses should work together.
 * For instance in a CQRS environment all events can be buffered until the command is processed.
 *
 * <p> If the message implements {@link NonBlockable} interface, it will never be buffered.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class BlockerInterceptor extends BaseObject implements DispatchInterceptor
{
    private $blocking = false;

    /**
     * @var InterceptorChain[]
     */
    private $chains = [];

    /**
     * @var BlockerInterceptorManager
     */
    private $manager;

    public function __construct()
    {
        $this->manager = new BlockerInterceptorManager($this);
    }

    public function invoke($message, InterceptorChain $chain) : void
    {
        if ($this->blocking && !($message instanceof NonBlockable)) {
            $this->chains[] = $chain;
        } else {
            $chain->proceed();
        }
    }

    public function startBlocking() : void
    {
        $this->blocking = true;
        self::getLogger()->debug('Message blocking has been started');
    }

    public function stopBlocking() : void
    {
        $this->blocking = false;
        self::getLogger()->debug('Message blocking has been stopped');
    }

    public function flush() : void
    {
        $chains = $this->chains;
        $this->chains = [];
        foreach ($chains as $chain) {
            $chain->proceed();
        }
        self::getLogger()->debug('Blocked chains have been flushed, {} item(s) was sent', [count($chains)]);
    }

    public function clear() : void
    {
        $this->chains = [];
        self::getLogger()->debug('Blocked chains have been cleared');
    }

    /**
     * @return BlockerInterceptorManager
     */
    public function manager() : BlockerInterceptorManager
    {
        return $this->manager;
    }
}
