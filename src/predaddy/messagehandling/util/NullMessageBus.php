<?php
declare(strict_types=1);

namespace predaddy\messagehandling\util;

use Closure;
use precore\lang\BaseObject;
use predaddy\messagehandling\MessageBus;
use predaddy\messagehandling\MessageCallback;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 * @codeCoverageIgnore
 */
final class NullMessageBus extends BaseObject implements MessageBus
{
    /**
     * Post a message on this bus. It is dispatched to all subscribed handlers.
     * MessageCallback will be notified with each message handler calls.
     *
     * MessageCallback is not necessarily supported by all implementations!
     *
     * @param object $message
     * @param MessageCallback $callback
     * @return void
     * @throws \InvalidArgumentException If $message is not an object
     */
    public function post($message, MessageCallback $callback = null) : void
    {
        self::getLogger()->warn("Calling '{}' will do nothing.", [__METHOD__]);
    }

    /**
     * Register the given handler to this bus. When registered, it will receive all messages posted to this bus.
     *
     * @param mixed $handler
     * @return void
     */
    public function register($handler) : void
    {
        self::getLogger()->warn("Calling '{}' will do nothing.", [__METHOD__]);
    }

    /**
     * Un-register the given handler to this bus.
     * When unregistered, it will no longer receive messages posted to this bus.
     *
     * @param object $handler
     * @return void
     */
    public function unregister($handler) : void
    {
        self::getLogger()->warn("Calling '{}' will do nothing.", [__METHOD__]);
    }

    /**
     * @param Closure $closure
     * @param int $priority Used to sort handlers when dispatching messages
     * @return void
     */
    public function registerClosure(Closure $closure, int $priority = self::DEFAULT_PRIORITY) : void
    {
        self::getLogger()->warn("Calling '{}' will do nothing.", [__METHOD__]);
    }

    /**
     * @param Closure $closure
     * @param int $priority
     * @return void
     */
    public function unregisterClosure(Closure $closure, int $priority = self::DEFAULT_PRIORITY) : void
    {
        self::getLogger()->warn("Calling '{}' will do nothing.", [__METHOD__]);
    }

    public function __toString() : string
    {
        return __CLASS__;
    }
}
