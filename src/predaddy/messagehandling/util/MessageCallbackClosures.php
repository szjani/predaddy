<?php
declare(strict_types=1);

namespace predaddy\messagehandling\util;

use Closure;
use Exception;
use precore\lang\BaseObject;
use predaddy\messagehandling\MessageCallback;

/**
 * Simple MessageCallback implementation which forwards message handler return values and thrown Exceptions
 * to the appropriate Closure if exists.
 *
 * You can build an instance with the builder() method which provides a builder
 * in order to be able to define optional callbacks.
 *
 * $messageCallback = MessageCallbackClosures::builder()
 *     ->successClosure($callback1)
 *     ->failureClosure($callback2)
 *     ->build();
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class MessageCallbackClosures extends BaseObject implements MessageCallback
{
    /**
     * @var Closure|null
     */
    private $onSuccessClosure;

    /**
     * @var Closure|null
     */
    private $onFailureClosure;

    /**
     * Use builder() method instead.
     */
    private function __construct()
    {
    }

    /**
     * @return MessageCallbackClosuresBuilder
     */
    public static function builder() : MessageCallbackClosuresBuilder
    {
        return new MessageCallbackClosuresBuilder();
    }

    /**
     * @param MessageCallbackClosuresBuilder $builder
     * @return MessageCallbackClosures
     */
    public static function build(MessageCallbackClosuresBuilder $builder) : MessageCallbackClosures
    {
        $callback = new MessageCallbackClosures();
        $callback->onSuccessClosure = $builder->getSuccessClosure();
        $callback->onFailureClosure = $builder->getFailureClosure();
        return $callback;
    }

    public function onSuccess($result) : void
    {
        if ($this->onSuccessClosure !== null) {
            call_user_func($this->onSuccessClosure, $result);
        }
    }

    public function onFailure(Exception $exception) : void
    {
        if ($this->onFailureClosure !== null) {
            call_user_func($this->onFailureClosure, $exception);
        }
    }
}
