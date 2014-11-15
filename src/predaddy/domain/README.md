CQRS / Event Sourcing
---------------------

### Commands and Events

It is required to implement `Command` interface in command and `DomainEvent` interface in domain event classes. Since these interfaces extend `ObjectInterface`, it is recommended to extend `Object`, which can be found in precore library.

All commands store the ID of the appropriate aggregate. Command handlers are responsible to create/load aggregates, modify their state and persist them.

`DomainEvent` also knows the ID of the aggregate, but it also provide a state hash. It must be the same as in the aggregate, which should be changed all the time, after the state of the aggregate is modified.
It is intended to solve lost update problem: http://www.w3.org/1999/04/Editing/. If an aggregate must be validated, the command should implement `StateHashAware` interface.

If a domain event extends `AbstractDomainEvent` and an AR extends `AbstractAggregateRoot`, both the ID and state hash parameters can be omitted during event object construction since they will be populated automatically.

### Process

If a command is a `DirectCommand` and `DirectCommandBus` is being used, predaddy automatically does the following things:
 1. If the aggregate ID is null in the command, predaddy assumes a new aggregate need to be created.
 2. Otherwise the appropriate aggregate will be loaded from the given `Repository`, and the command will be forwarded to the aggregate.
 3. If the command implements `StateHashAware` interface, the aggregate state will be validated.

If the command handler process for a particular `Command` is complicated, a specific command handler can be registered to the command bus. In this case, predaddy will
forward the command to the handler and will not do anything.

The CQRS example uses `CommandBus`, command will not be forwarded to the aggregate. However, the second example applies commands directly on the aggregate.
The read storage synchronization is not part of the examples below.

### CQRS

The following example uses annotation based configuration.

#### Configuration

```php
// you can use any TransactionManager implementation, see trf4php
$transactionManager = new DoctrineTransactionManager($entityManager);

$trBuses = TransactionalBusesBuilder::create($transactionManager)->build();

$eventBus = $trBuses->eventBus();
$commandBus = $trBuses->commandBus();

// register the command handlers
$commandBus->register(new UserCommandHandler($userRepository));

// register the event handlers
$eventBus->register(new UserEventHandler());
```

Hint: `Repository` interface is required only for `DirectCommandBus` and EventSourced aggregates. `$userRepository` in this example can
implement a domain specific, customized `UserRepository` interface.

#### The domain model

```php
class User extends AbstractAggregateRoot
{
    /**
     * Unique identifier
     *
     * @var string
     */
    private $userId;
    
    /**
     * @var string
     */
    private $email;

    public function getId()
    {
        return UserId::from($this->userId);
    }

    public function modifyEmailAddress($email)
    {
        // validate parameters, throw exception if necessary
        Assert::email($email);
        $this->email = $email;
        $this->raise(new UserEmailModified($email));
    }
}
```

It is a good practise to use aggregate specific ID value objects.

```php
final class UserId extends UUIDAggregateId
{
    public function aggregateClass()
    {
        return User::className();
    }
}
```

Hint: If your persistence library cannot manage `AggregateId` object within the aggregate roots, you can store only their value in the ARs,
and you can recreate the ID object in the `getId()` method all the time, like in the example above.

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

Hint: If you would like to handle an event immediately, within the same transaction, the event class just need to
implement `NonBlockable` interface.

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
        $user = $this->userRepository->load(UserId::from($command->aggregateId()));
        
        // checking the aggregate state (optional)
        $user->failWhenStateHashViolation($command->stateHash());
        
        $user->modifyEmailAddress($command->getEmail());
    }
}
```

#### Sending a command

```php
$commandBus->post(new ModifyEmail($userId, $email));
```

Hint: If you would like to validate commands before they are being processed, take a look at [predaddy-symfony-validator component](https://github.com/szjani/predaddy-symfony-validator).

### Event Sourcing

The following configuration provides you a direct command passing process, so commands are being sent directly to the aggregate roots. As you can see it uses Doctrine implementation of `TransactionManager` and `EventStore`.

You can define your snapshotting strategy with a `SnapshotStrategy` implementation. The example below never creates snapshots.

Even if you use `DirectCommandBus`, you can register explicit command handlers so you have the chance to do complex processes if needed.

#### Configuration

```php
$eventStore = new DoctrineOrmEventStore($entityManager);
$trBuses = TransactionalBusesBuilder::create(new DoctrineTransactionManager($entityManager))
    ->interceptEventsWithinTransaction([new EventPersister($eventStore)])
    ->withRepository(new EventSourcingRepository($eventStore))
    ->build();
$eventBus = $trBuses->eventBus();
$commandBus = $trBuses->commandBus();
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
        // UserId extends UUIDAggregateId
        $this->apply(new UserCreated(UserId::create()));
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
        $this->apply(new IncrementedEvent());
    }

    /**
     * @Subscribe
     */
    private function handleCreated(UserCreated $event)
    {
        $this->id = $event->aggregateId();
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
        $aggregateId = $event->aggregateId();
    }
);

$commandBus->post(new CreateEventSourcedUser());
$commandBus->post(new Increment($aggregateId->value()));
$commandBus->post(new Increment($aggregateId->value()));
```

### EventStores and Repositories

You can find memory based implementations under `inmemory` directory, which might be useful for testing purposes. If you would like to use predaddy with Doctrine ORM,
take a look at [predaddy-doctrine-orm](https://github.com/szjani/predaddy-doctrine-orm) component. It provides `DoctrineOrmEventStore` and `DoctrineAggregateRootRepository`.

#### Serialization

You can specify how your objects (snapshots and events) should be serialized in the event store. There are some serializer strategies in `serializer`.
In order to properly configure `JmsSerializer`, use the XML config files located under `/src/resources/jms` directory.
