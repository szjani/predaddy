<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;

/**
 * Builder for {@link SimpleMessageBus}.
 *
 * @package predaddy\messagehandling
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class SimpleMessageBusBuilder
{
    const DEFAULT_NAME = 'message-bus';

    /**
     * @var AnnotatedMessageHandlerDescriptorFactory
     */
    private static $defaultHandlerDescFactory;

    /**
     * @var NullSubscriberExceptionHandler
     */
    private static $defaultExceptionHandler;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var DispatchInterceptor[]
     */
    private $interceptors = [];

    /**
     * @var SubscriberExceptionHandler
     */
    private $exceptionHandler;

    /**
     * @var MessageHandlerDescriptorFactory
     */
    private $handlerDescriptorFactory;

    public function __construct()
    {
        $this->exceptionHandler = static::defaultExceptionHandler();
        $this->handlerDescriptorFactory = static::defaultHandlerDescFactory();
        $this->identifier = static::defaultName();
    }

    /**
     * Should not be called!
     */
    public static function init() : void
    {
        self::$defaultHandlerDescFactory = new AnnotatedMessageHandlerDescriptorFactory(
            new DefaultFunctionDescriptorFactory()
        );
        self::$defaultExceptionHandler = new NullSubscriberExceptionHandler();
    }

    /**
     * Override if the default name should be modified.
     *
     * @return string
     */
    protected static function defaultName() : string
    {
        return self::DEFAULT_NAME;
    }

    /**
     * Override if the default factory should be modified.
     *
     * @return AnnotatedMessageHandlerDescriptorFactory
     */
    protected static function defaultHandlerDescFactory() : MessageHandlerDescriptorFactory
    {
        return self::$defaultHandlerDescFactory;
    }

    /**
     * Override if the default exception handler should be modified.
     *
     * @return NullSubscriberExceptionHandler
     */
    protected static function defaultExceptionHandler() : SubscriberExceptionHandler
    {
        return self::$defaultExceptionHandler;
    }

    /**
     * @return SimpleMessageBus
     */
    public function build()
    {
        return new SimpleMessageBus($this);
    }

    /**
     * @param $identifier
     * @return $this
     */
    public function withIdentifier(string $identifier) : self
    {
        $this->identifier = (string) $identifier;
        return $this;
    }

    /**
     * @param DispatchInterceptor[] $interceptors
     * @return $this
     */
    public function withInterceptors(array $interceptors) : self
    {
        $this->interceptors = $interceptors;
        return $this;
    }

    /**
     * @param SubscriberExceptionHandler $exceptionHandler
     * @return $this
     */
    public function withExceptionHandler(SubscriberExceptionHandler $exceptionHandler) : self
    {
        $this->exceptionHandler = $exceptionHandler;
        return $this;
    }

    /**
     * @param MessageHandlerDescriptorFactory $descriptorFactory
     * @return $this
     */
    public function withHandlerDescriptorFactory(MessageHandlerDescriptorFactory $descriptorFactory) : self
    {
        $this->handlerDescriptorFactory = $descriptorFactory;
        return $this;
    }

    /**
     * @return SubscriberExceptionHandler
     */
    public function getExceptionHandler() : SubscriberExceptionHandler
    {
        return $this->exceptionHandler;
    }

    /**
     * @return MessageHandlerDescriptorFactory
     */
    public function getHandlerDescriptorFactory() : MessageHandlerDescriptorFactory
    {
        return $this->handlerDescriptorFactory;
    }

    /**
     * @return string
     */
    public function getIdentifier() : string
    {
        return $this->identifier;
    }

    /**
     * @return DispatchInterceptor[]
     */
    public function getInterceptors() : array
    {
        return $this->interceptors;
    }
}
SimpleMessageBusBuilder::init();
