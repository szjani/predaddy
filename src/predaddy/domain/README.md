CQRS / Event Sourcing
---------------------

There are two ways to handle commands:
 1. Creating command handlers which must be implemented
 2. Forwarding the commands directly to the aggregates

All commands store the ID of the aggregate which should be passed to, and the current version of the aggregate. The version is used for optimistic locking avoiding concurrency issues. If the command is a create command, ID and version field must be null and 0 correspondingly.

The CQRS example follows the first, the ES follows the second approach. The read storage synchronization is not part of the examples below.

### CQRS

The following example uses annotation based configuration.

#### Configuration

```php
// you can use any ObservableTransactionManager implementation, see trf4php
$transactionManager = new DoctrineTransactionManager($entityManager);

/* Event bus is synchronized to transactions, so you should use it
with the same TransactionManager which is used by the command bus.
The following setup provide you an annotation based configuration. */

// configure event bus
$eventHandlerDescFactory = new AnnotatedMessageHandlerDescriptorFactory(
    new EventFunctionDescriptorFactory()
);
$domainEventBus = new EventBus($eventHandlerDescFactory, $transactionManager);

// configure the repository to save and load aggregates
$userRepository = new UserRepository($domainEventBus);

// configure command bus
$commandHandlerDescFactory = new AnnotatedMessageHandlerDescriptorFactory(
    new CommandFunctionDescriptorFactory()
);
$commandBus = new CommandBus($commandHandlerDescFactory, $transactionManager);

// register the command handlers
$commandBus->register(new UserCommandHandler($userRepository));

// register the event handlers
$domainEventBus->register(new UserEventHandler());
```

#### The domain model

```php
class User extends AbstractAggregateRoot
{
    private $id;
    private $email;

    /**
     * @var int Should be used for locking
     */
    private $version;

    // some missing methods

    public function modifyEmailAddress($email)
    {
        // validate parameters, throw exception if necessary
        Assert::email($email);
        $this->email = $email;
        $this->raise(new UserEmailModified($this->getId(), $email, $this->version));
    }
}
```

#### An event handler

```php
class UserEventHandler
{
    /**
     * @Subscribe
     * will be called just after the transaction has been committed
     */
    public function handleEmailModification(UserEmailModified $event)
    {
        // other tasks, fire new commands, update read model, etc.
    }
}
```

#### The command handler

```php
class UserCommandHandler
{
    /**
     * @Subscribe
     * will be called and wrapped in a transaction
     */
    public function handleCommand(ModifyEmail $command)
    {
        // somehow obtain the persistent aggregate root
        $user = $this->userRepository->load($command->getAggregateId());
        $user->modifyEmailAddress($command->getEmail());
        $this->userRepository->save($user, $command->getVersion());
    }
}
```

#### The repository

You need to create repository implementations for all of your aggregates. Extending `AggregateRootRepository` provides
you the feature that all events raised in the given aggregate are being forwarded to the event bus when you are saving it.

```php
class UserRepository extends AggregateRootRepository
{
    public function __construct(EventBus $eventBus)
    {
        parent::__construct($eventBus);
    }

    protected function innerSave(AggregateRoot $aggregateRoot, Iterator $events, $version)
    {
    }

    public function load(AggregateId $aggregateId)
    {
    }
}
```

#### Sending a command

```php
$commandBus->post(new ModifyEmail($userId, $email, $version));
```

### Event Sourcing

The following configuration provides you a direct command passing process, so commands are being sent directly to the aggregate roots. As you can see it uses Doctrine implementation of `ObservableTransactionManager` and `EventStore`.

You can define your snapshotting strategy with a `SnapshotStrategy` implementation. The example below never creates snapshots.

#### Configuration

```php
// $transactionManager can be any implementation of ObservableTransactionManager
$transactionManager = new DoctrineTransactionManager($entityManager);
$eventBus = new EventBus(
    new AnnotatedMessageHandlerDescriptorFactory(new EventFunctionDescriptorFactory()),
    $transactionManager
);
// you can use any EventStore implementation, DoctrineOrmEventStore is a builtin class
$eventStore = new DoctrineOrmEventStore($entityManager);
$commandHandlerDescFactory = new AnnotatedMessageHandlerDescriptorFactory(new CommandFunctionDescriptorFactory());
$commandBus = new DirectCommandBus(
    $commandHandlerDescFactory,
    $transactionManager,
    new LazyEventSourcedRepositoryRepository($eventBus, $eventStore, TrivialSnapshotStrategy::$NEVER),
    new SimpleMessageBusFactory($commandHandlerDescFactory)
);
```

#### Model

```php
class EventSourcedUser extends AbstractEventSourcedAggregateRoot
{
    const DEFAULT_VALUE = 1;

    private $id;
    private $value = self::DEFAULT_VALUE;

    /**
     * @Subscribe
     * @param CreateEventSourcedUser $command
     */
    public function __construct(CreateEventSourcedUser $command)
    {
        $this->apply(new UserCreated(new UUIDAggregateId(UUID::randomUUID()), $command->getVersion()));
    }

    /**
     * @return AggregateId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @Subscribe
     * @param Increment $command
     */
    public function increment(Increment $command)
    {
        $this->apply(new IncrementedEvent($this->id, $command->getVersion()));
    }

    /**
     * @Subscribe
     */
    private function handleCreated(UserCreated $event)
    {
        $this->id = $event->getUserId();
    }

    /**
     * @Subscribe
     */
    private function handleIncrementedEvent(IncrementedEvent $event)
    {
        $this->value++;
    }
}
```

#### Sending commands

```php
// catch the aggregate ID generated inside the AR
$aggregateId = null;
$eventBus->registerClosure(
    function (UserCreated $event) use (&$aggregateId) {
        $aggregateId = $event->getAggregateIdentifier();
    }
);

$commandBus->post(new CreateEventSourcedUser());
$commandBus->post(new Increment($aggregateId->getValue(), 1));
$commandBus->post(new Increment($aggregateId->getValue(), 2));
```
