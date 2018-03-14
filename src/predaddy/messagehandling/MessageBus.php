<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use Closure;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface MessageBus
{
    const DEFAULT_PRIORITY = 1;

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
    public function post($message, MessageCallback $callback = null) : void;

    /**
     * Register the given handler to this bus. When registered, it will receive all messages posted to this bus.
     *
     * @param mixed $handler
     * @return void
     */
    public function register($handler) : void;

    /**
     * Un-register the given handler to this bus.
     * When unregistered, it will no longer receive messages posted to this bus.
     *
     * @param object $handler
     * @return void
     */
    public function unregister($handler) : void;

    /**
     * @param Closure $closure
     * @param int $priority Used to sort handlers when dispatching messages
     * @return void
     */
    public function registerClosure(Closure $closure, int $priority = self::DEFAULT_PRIORITY) : void;

    /**
     * @param Closure $closure
     * @param int $priority
     * @return void
     */
    public function unregisterClosure(Closure $closure, int $priority = self::DEFAULT_PRIORITY) : void;
}
