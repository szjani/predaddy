CQRS / Event Sourcing
---------------------

### Commands and Events

It is required to implement `Command` interface in command and `DomainEvent` interface in domain event classes. Since these interfaces extend `ObjectInterface`, it is recommended to extend `Object` in your classes which can be also found in precore library.

All commands store the ID of the aggregate which should be passed to, and the current state hash of the aggregate. Both parameters are optional. ID should be NULL if the purpose of the command is creating a new aggregate. The state hash field can be used to define which version of the aggregate the user has operated on. This check must be enforced by the command handler. If you don't need it, just leave it when you instantiate the command class.

These values also occur in domain event objects. If a domain event extends `AbstractDomainEvent` and an AR extends `AbstractAggregateRoot`, both parameters can be omitted during event object construction since they will be populated automatically.

### Command handling

There are two ways to handle commands:
 1. You create your own command handlers
 2. predaddy forwards all commands directly to the appropriate aggregate

You can mix these ways. Even if your application is configured to use the second approach, you can register your own command handlers in order to override the builtin process.

The CQRS example follows the first, the ES follows the second approach. The read storage synchronization is not part of the examples below.

### CQRS

The following example uses annotation based configuration.

#### Configuration

```php
// you can use any ObservableTransactionManager implementation, see trf4php
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

The following configuration provides you a direct command passing process, so commands are being sent directly to the aggregate roots. As you can see it uses Doctrine implementation of `ObservableTransactionManager` and `EventStore`.

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

### EventStore

There is one builtin `EventStore` implementation: `DoctrineOrmEventStore`. It uses 3 entities for storing aggregates, events and snapshots.
You have to create database tables from these entities, `doctrine.php` CLI tool can be used for it.

#### Serialization

You can specify how your objects (snapshots and events) should be serialized with a `Serializer` object which can be passed as a constructor parameter.
`DoctrineOrmEventStore` uses simple PHP serialization by default, but there are two other implementations: `ReflectionSerializer` and `JmsSerializer`.
There are some XML config files for the latter one in the `/src/resources/jms` directory which can be used to configure it.
