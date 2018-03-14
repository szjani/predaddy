<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use precore\lang\BaseObject;
use precore\util\Objects;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class SubscriberExceptionContext extends BaseObject
{
    private $messageBus;
    private $message;
    private $callableWrapper;

    public function __construct(MessageBus $messageBus, $message, CallableWrapper $callableWrapper)
    {
        $this->callableWrapper = $callableWrapper;
        $this->message = $message;
        $this->messageBus = $messageBus;
    }

    /**
     * @return CallableWrapper
     */
    public function getCallableWrapper() : CallableWrapper
    {
        return $this->callableWrapper;
    }

    /**
     * @return object
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return MessageBus
     */
    public function getMessageBus() : MessageBus
    {
        return $this->messageBus;
    }

    public function toString() : string
    {
        return Objects::toStringHelper($this)
            ->add('bus', $this->messageBus)
            ->add('message', $this->message)
            ->add('wrapper', $this->callableWrapper)
            ->toString();
    }
}
