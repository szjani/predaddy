<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use ArrayIterator;
use Closure;
use InvalidArgumentException;
use precore\lang\BaseObject;
use precore\util\Preconditions;
use predaddy\messagehandling\util\MessageCallbackClosures;
use RuntimeException;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class InterceptableMessageBus extends BaseObject implements MessageBus
{
    /**
     * @var MessageCallback
     */
    private static $emptyCallback;

    /**
     * @var DispatchInterceptor[]
     */
    private $interceptors;

    /**
     * Should not be called directly!
     */
    public static function init() : void
    {
        self::$emptyCallback = MessageCallbackClosures::builder()->build();
    }

    public function __construct(array $interceptors = [])
    {
        $this->interceptors = $interceptors;
    }

    /**
     * Dispatches the message to all handlers.
     *
     * All exceptions thrown by handlers must be caught and should be forwarded to $callback.
     *
     * @param $message
     * @param MessageCallback $callback
     * @return void
     */
    abstract protected function dispatch($message, MessageCallback $callback) : void;

    /**
     * Post a message on this bus. It is dispatched to all subscribed handlers.
     * MessageCallback will be notified with each message handler calls.
     *
     * MessageCallback is not necessarily supported by all implementations!
     *
     * @param object $message
     * @param MessageCallback $callback
     * @return void
     * @throws InvalidArgumentException If $message is not an object
     * @throws RuntimeException that may be thrown by an interceptor
     */
    final public function post($message, MessageCallback $callback = null) : void
    {
        Preconditions::checkArgument(is_object($message), 'Incoming message is not an object');
        if ($callback === null) {
            $callback = self::emptyCallback();
        }
        $dispatchClosure = function () use ($message, $callback) {
            $this->dispatch($message, $callback);
        };
        $this->createChain($message, $dispatchClosure)->proceed();
    }

    /**
     * @return MessageCallback
     */
    final protected static function emptyCallback() : MessageCallback
    {
        return self::$emptyCallback;
    }

    /**
     * @param $message
     * @param Closure $dispatchClosure
     * @return InterceptorChain
     */
    private function createChain($message, Closure $dispatchClosure) : InterceptorChain
    {
        return new DefaultInterceptorChain(
            $message,
            new ArrayIterator($this->interceptors),
            $dispatchClosure
        );
    }
}
InterceptableMessageBus::init();
