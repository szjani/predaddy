<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use ArrayObject;
use Closure;
use Exception;
use precore\util\Collections;
use precore\util\Objects;
use SplHeap;
use SplObjectStorage;

/**
 * {@link MessageBus} which find handler methods in the registered message handlers.
 *
 * Handler method finding mechanism can be modified with
 * {@link MessageHandlerDescriptorFactory} and {@link FunctionDescriptorFactory} instances
 * through the builder object.
 *
 * It manages {@link PropagationStoppable} messages properly.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class SimpleMessageBus extends InterceptableMessageBus implements HandlerFactoryRegisterableMessageBus
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var MessageHandlerDescriptorFactory
     */
    private $handlerDescriptorFactory;

    /**
     * @var FunctionDescriptorFactory
     */
    private $closureDescriptorFactory;

    /**
     * @var SplObjectStorage
     */
    private $factories;

    /**
     * @var SubscriberExceptionHandler
     */
    private $exceptionHandler;

    /**
     * @var SplObjectStorage
     */
    private $functionDescriptors;

    /**
     * @param SimpleMessageBusBuilder $builder
     */
    public function __construct(SimpleMessageBusBuilder $builder = null)
    {
        if ($builder === null) {
            $builder = self::builder();
        }
        parent::__construct($builder->getInterceptors());
        $this->identifier = $builder->getIdentifier();
        $this->exceptionHandler = $builder->getExceptionHandler();
        $this->handlerDescriptorFactory = $builder->getHandlerDescriptorFactory();
        $this->closureDescriptorFactory = $builder->getHandlerDescriptorFactory()->getFunctionDescriptorFactory();
        $this->functionDescriptors = new SplObjectStorage();
        $this->factories = new SplObjectStorage();
    }

    /**
     * @return SimpleMessageBusBuilder
     */
    public static function builder()
    {
        return new SimpleMessageBusBuilder();
    }

    public function registerHandlerFactory(Closure $factory) : void
    {
        $descriptor = $this->closureDescriptorFactory->create(new ClosureWrapper($factory), self::DEFAULT_PRIORITY);
        $this->factories->attach($factory, $descriptor);
    }

    public function unregisterHandlerFactory(Closure $factory) : void
    {
        $this->factories->detach($factory);
    }

    /**
     * @param object $handler
     */
    public function register($handler) : void
    {
        $descriptor = $this->handlerDescriptorFactory->create($handler);
        foreach ($descriptor->getFunctionDescriptors() as $functionDescriptor) {
            $this->functionDescriptors->attach($functionDescriptor);
        }
    }

    /**
     * @param object $handler
     */
    public function unregister($handler) : void
    {
        $descriptor = $this->handlerDescriptorFactory->create($handler);
        foreach ($descriptor->getFunctionDescriptors() as $functionDescriptor) {
            $this->functionDescriptors->detach($functionDescriptor);
        }
    }

    /**
     * @param Closure $closure
     * @param int $priority
     */
    public function registerClosure(Closure $closure, int $priority = self::DEFAULT_PRIORITY) : void
    {
        $descriptor = $this->closureDescriptorFactory->create(new ClosureWrapper($closure), $priority);
        $this->functionDescriptors->attach($descriptor);
    }

    /**
     * @param Closure $closure
     * @param int $priority
     */
    public function unregisterClosure(Closure $closure, int $priority = self::DEFAULT_PRIORITY) : void
    {
        $descriptor = $this->closureDescriptorFactory->create(new ClosureWrapper($closure), $priority);
        $this->functionDescriptors->detach($descriptor);
        foreach ($this->functionDescriptors as $key => $value) {
            if ($value->equals($descriptor)) {
                $this->functionDescriptors->offsetUnset($value);
                break;
            }
        }
    }

    /**
     * Dispatches $message to all handlers.
     *
     * @param $message
     * @param MessageCallback $callback
     * @return void
     */
    protected function dispatch($message, MessageCallback $callback) : void
    {
        $handled = false;
        foreach ($this->callableWrappersFor($message) as $callable) {
            $handled = true;
            if ($message instanceof PropagationStoppable && $message->isPropagationStopped()) {
                break;
            }
            try {
                $result = $callable->invoke($message);
                self::getLogger()->debug(
                    "The following message has been dispatched to handler '{}' through message bus '{}': {}",
                    [$callable, $this, $message]
                );
                if ($result !== null) {
                    $callback->onSuccess($result);
                }
            } catch (Exception $exp) {
                self::getLogger()->warn(
                    "An error occurred in the following message handler through message bus '{}': {}, message is {}!",
                    [$this, $callable, $message],
                    $exp
                );
                $context = new SubscriberExceptionContext($this, $message, $callable);
                try {
                    $this->exceptionHandler->handleException($exp, $context);
                } catch (Exception $e) {
                    self::getLogger()->error(
                        "An error occurred in the exception handler with context '{}'",
                        [$context],
                        $e
                    );
                }
                try {
                    $callback->onFailure($exp);
                } catch (Exception $e) {
                    self::getLogger()->error("An error occurred in message callback on bus '{}'", [$this], $e);
                }
            }
        }
        if (!$handled && !($message instanceof DeadMessage)) {
            self::getLogger()->debug(
                "The following message as a DeadMessage is being posted to '{}' message bus: {}",
                [$this, $message]
            );
            $this->dispatch(new DeadMessage($message), $callback);
        }
    }

    /**
     * @param $message
     * @return ArrayObject
     */
    protected function callableWrappersFor($message) : ArrayObject
    {
        $heap = Collections::createHeap(Collections::reverseOrder());

        self::insertAllHandlers($this->functionDescriptors, $heap, $message);

        foreach ($this->factories as $factory) {
            /* @var $factoryDescriptor FunctionDescriptor */
            $factoryDescriptor = $this->factories[$factory];
            if ($factoryDescriptor->isHandlerFor($message)) {
                $handler = call_user_func($factory, $message);
                $descriptor = $this->handlerDescriptorFactory->create($handler);
                self::insertAllHandlers($descriptor->getFunctionDescriptors(), $heap, $message);
            }
        }

        $res = new ArrayObject();
        foreach ($heap as $functionDescriptor) {
            $res->append($functionDescriptor->getCallableWrapper());
        }

        return $res;
    }

    private static function insertAllHandlers($functionDescriptors, SplHeap $heap, $message) : void
    {
        /* @var $functionDescriptor FunctionDescriptor */
        foreach ($functionDescriptors as $functionDescriptor) {
            if ($functionDescriptor->isHandlerFor($message)) {
                $heap->insert($functionDescriptor);
            }
        }
    }

    public function toString() : string
    {
        return Objects::toStringHelper($this)
            ->add($this->identifier)
            ->toString();
    }
}
