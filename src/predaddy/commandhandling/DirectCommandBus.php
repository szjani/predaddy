<?php
declare(strict_types=1);

namespace predaddy\commandhandling;

use ArrayObject;
use Exception;
use precore\lang\ObjectClass;
use precore\util\Preconditions;
use predaddy\domain\GenericAggregateId;
use predaddy\domain\Repository;
use predaddy\domain\StateHashAware;
use predaddy\messagehandling\ClosureWrapper;
use predaddy\messagehandling\MessageHandlerDescriptorFactory;
use predaddy\messagehandling\util\SimpleMessageCallback;

/**
 * This class acts as a {@link CommandBus} expect one case. If the posted command is a {@link DirectCommand}
 * and there is no registered handler which could process that, it will load the appropriate AR from the given
 * repository and passes the command to that. This bus should be used if business method parameters
 * in the aggregates are {@link Command} objects.
 *
 * If you have specialized repositories for your aggregates, it is recommended to use {@link RepositoryDelegate}.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class DirectCommandBus extends CommandBus
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var MessageHandlerDescriptorFactory
     */
    private $handlerDescriptorFactory;

    /**
     * @param DirectCommandBusBuilder $builder
     */
    public function __construct(DirectCommandBusBuilder $builder)
    {
        parent::__construct($builder);
        $this->repository = $builder->getRepository();
        $this->handlerDescriptorFactory = $builder->getHandlerDescriptorFactory();
    }

    /**
     * The given repository cannot be null, the default value is due to PHP restrictions.
     *
     * @param Repository $repository Is being passed to the registered DirectCommandForwarder
     * @return DirectCommandBusBuilder
     */
    public static function builder(Repository $repository = null)
    {
        return new DirectCommandBusBuilder(Preconditions::checkNotNull($repository));
    }

    protected function callableWrappersFor($message) : ArrayObject
    {
        $wrappers = parent::callableWrappersFor($message);
        if (($message instanceof DirectCommand) && $wrappers->count() === 0) {
            $wrappers = new ArrayObject([new ClosureWrapper(
                function (DirectCommand $command) {
                    return $this->forwardCommand($command);
                }
            )]);
        }
        return $wrappers;
    }

    /**
     * @param DirectCommand $command
     * @throws \Exception If the handler throws any
     * @return mixed The return value of the last handler (should be one handler per aggregate)
     */
    private function forwardCommand(DirectCommand $command)
    {
        $aggregateClass = $command->aggregateClass();
        $aggregateId = $command->aggregateId();
        if ($aggregateId === null) {
            $aggregate = ObjectClass::forName($aggregateClass)->newInstanceWithoutConstructor();
            self::getLogger()->debug('New aggregate [{}] has been created', [$aggregateClass]);
        } else {
            $aggregate = $this->repository->load(new GenericAggregateId($aggregateId, $aggregateClass));
            self::getLogger()->debug(
                'Aggregate [{}] with ID [{}] has been successfully loaded',
                [$aggregateClass, $aggregateId]
            );
            if ($command instanceof StateHashAware) {
                $aggregate->failWhenStateHashViolation($command->stateHash());
            }
        }
        $forwarderBus = CommandBus::builder()
            ->withIdentifier($aggregateClass)
            ->withHandlerDescriptorFactory($this->handlerDescriptorFactory)
            ->build();
        $forwarderBus->register($aggregate);
        $callback = new SimpleMessageCallback();
        $forwarderBus->post($command, $callback);
        $thrownException = $callback->getException();
        if ($thrownException instanceof Exception) {
            self::getLogger()->debug('Error occurred when command has been applied [{}]', [$command], $thrownException);
            throw $thrownException;
        }
        $this->repository->save($aggregate);
        self::getLogger()->info("Command [{}] has been applied", [$command]);
        return $callback->getResult();
    }
}
