CQRS / Event Sourcing
---------------------

### Recommended CQRS usage (without read side)

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
class User extends AggregateRoot
{
    private $id;
    private $email;

    // some missing methods

    public function modifyEmailAddress($email)
    {
        // validate parameters, throw exception if necessary
        Assert::email($email);
        $this->email = $email;
        $this->raise(new UserEmailModified($this->getId(), $email));
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
