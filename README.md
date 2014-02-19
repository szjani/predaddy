predaddy
========
[![Latest Stable Version](https://poser.pugx.org/predaddy/predaddy/v/stable.png)](https://packagist.org/packages/predaddy/predaddy)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/szjani/predaddy/badges/quality-score.png?s=496589a983254d22b4334552572b833061b9bd03)](https://scrutinizer-ci.com/g/szjani/predaddy/)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/ad36fc7a-f48d-4919-b20d-90eae34aecd9/mini.png)](https://insight.sensiolabs.com/projects/ad36fc7a-f48d-4919-b20d-90eae34aecd9)

master: [![Build Status](https://travis-ci.org/szjani/predaddy.png?branch=master)](https://travis-ci.org/szjani/predaddy) [![Coverage Status](https://coveralls.io/repos/szjani/predaddy/badge.png?branch=master)](https://coveralls.io/r/szjani/predaddy?branch=master)
1.2: [![Build Status](https://travis-ci.org/szjani/predaddy.png?branch=1.2)](https://travis-ci.org/szjani/predaddy) [![Coverage Status](https://coveralls.io/repos/szjani/predaddy/badge.png?branch=1.2)](https://coveralls.io/r/szjani/predaddy?branch=1.2)

It is a library which gives you some usable classes to be able to use common DDD patterns.
You can find some examples in the [sample directory](https://github.com/szjani/predaddy/tree/master/sample).

Predaddy uses [lf4php](https://github.com/szjani/lf4php) for logging.

MessageBus
----------

MessageBus provides a general interface for message handling. The basic concept is that message handlers can
be registered to the bus which forwards each incoming messages to the appropriate handler. Message handlers
can be either objects or closures.

SimpleMessageBus is a basic implementation of the MessageBus interface. Currently, all other MessageBus implementations extend this class.

If you use CQRS, then I highly recommend to use the pre-configured `EventBus` and `CommandBus` classes.
For more information, please scroll down.

### Handler methods/functions

Predaddy is quite configurable, but it has several default behaviours. Handler functions/methods should have one parameter with typehint.
The typehint defines which `Message` objects can be handled, by default. If you want to handle all `Message` objects,
you just have to use `Message` typehint. This kind of solution provides an easy way to use and distinguish a huge amount of
message classes. Interface and abstract class typehints also work as expected.

### Annotations

You can use your own handler method scanning/defining process, however the system does support annotation based configuration.
It means that you just have to mark handler methods in your handler classes with `@Subject` annotation. When you register an instance
of this class, predaddy is automatically finding these methods.

### Interceptors

It's possible to extend bus behaviour when messages are being dispatched to message handlers. `HandlerInterceptor` objects wrap
the concrete dispatch process and are able to modify that. It is usable for logging, transactions, etc.

There is one builtin interceptor: `TransactionInterceptor`. If you pass it to a `MessageBus`, all message dispatch processes
will be wrapped into a separated transaction.

### CommandBus

`TransactionInterceptor` is already registered which indicates that all event handlers are wrapped by a unique transaction.
`Message` objects must implement `Command` interface. The typehint in the handler methods must be exactly the same as the command object's type.

### EventBus

This message bus implementation uses the default typehint handling (subclass handling, etc.). Message objects
must implement `Event` interface. Messages are buffered until the transaction is committed. It extends `Mf4phpMessageBus` class
and uses `TransactedMemoryMessageDispatcher`.

### Mf4phpMessageBus

`Mf4phpMessageBus` wraps a `MessageDispatcher`, so all features provided by [mf4php](https://github.com/szjani/mf4php) can be achieved with this class, such as
synchronize messages to transactions and asynchronous event dispatching. For further information see the [mf4php](https://github.com/szjani/mf4php) documentation.

### Recommended CQRS usage (without read side)

The following example uses annotation based configuration.

#### Configuration

```php
// you can use any ObservableTransactionManager implementation, see trf4php
$transactionManager = new DoctrineTransactionManager($entityManager);

/* Event bus should be synchronized to transactions, so you should use it
with the same TransactionManager which is used in the command bus.
The following setup provide you an annotation based message listening. */

// configure event bus
$eventFuncHandlerDescFactory = new EventFunctionDescriptorFactory();
$eventHandlerDescFactory = new AnnotatedMessageHandlerDescriptorFactory(
    $eventFuncHandlerDescFactory
);
$domainEventBus = new EventBus(
    'domain-event-bus',
    $eventHandlerDescFactory,
    $eventFuncHandlerDescFactory,
    $transactionManager
);

// use the configured $domainEventBus in all aggregate root
AggregateRoot::setEventBus($domainEventBus);

// configure command bus
$commandFuncHandlerDescFactory = new CommandFunctionDescriptorFactory();
$commandHandlerDescFactory = new AnnotatedMessageHandlerDescriptorFactory(
    $commandFuncHandlerDescFactory
);
$commandBus = new CommandBus(
    'command-bus',
    $commandHandlerDescFactory,
    $commandFuncHandlerDescFactory,
    $transactionManager
);

// register the command handlers
$commandBus->register(new UserCommandHandler());

// register the event handlers
$domainEventBus->register(new UserEventHandler());
```

#### The domain model

```php
class User extends AggregateRoot
{
    private $id;
    private $email;

    public function modifyEmailAddress($email)
    {
        // validate parameters, throw exception if necessary
        Assert::email($email);
        $this->raise(new UserEmailModified($this->id, $email));
    }

    /**
     * @Subscribe
     */
    private function handleEmailModification(UserEmailModified $event)
    {
        // no validation
        $this->email = $event->getEmail();
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
        $user = $this->userRepository->find($command->getUserId());
        $user->modifyEmailAddress($command->getEmail());
        $this->userRepository->store($user, $command->getVersion());
    }
}
```

#### Sending a command

```php
$commandBus->post(new ModifyEmail($userId, $email, $version));
```

Predaddy gives an `AggregateRoot` class which provides some opportunities. You should validate incoming parameters in
public methods and if everything is fine, fire a domain event. `AggregateRoot` passes this event immediately to the handler method
inside the aggregate root (which visibility must be protected or private). After that, this event will be passed to the $domainEventBus,
which is probably synchronized to transactions. Fetching the existing user object from a persistent storage and calling the modifyEmailAddress method
are a command handler's responsibilities. After the command bus has committed the transaction, the event will be dispatched to the proper
event handlers registered in $domainEventBus. These handlers might send another commands to achieve eventually consistency.

Paginator components
--------------------

It is a common thing to use paginated results on the user interface. The `presentation` package provides you some useful
classes and interfaces. You can integrate them into your MVC framework to be able to feed them with data coming from the
request. This `Pageable` object can be used in your repository or in any other data provider object as a parameter which can return a `Page` object.
A short example:

```php
$page = 2;
$size = 10;
$sort = Sort::create('name', Direction::$DESC);
$request = new PageRequest($page, $size, $sort);

/* @var $page Page */
$page = $userFinder->getPage($request);
```

In the above example the `$page` object stores the users and a lot of information to be able to create a paginator on the UI.

History
-------

### 1.2

#### Event handling has been refactored

Classes have been moved to a different namespace, you have to modify `use` your statements.

#### Interceptor support added

Take a look at [TransactionInterceptor](https://github.com/szjani/predaddy/blob/1.2/src/predaddy/messagehandling/interceptors/TransactionInterceptor.php).

#### No more marker interface for handler classes

Handler classes do not have to implement any interfaces. You have to remove `implements EventHandler` parts from your classes.

#### MessageCallback added

You can register a callback class for a message post to be able to catch the result.

### 1.1

#### Allowed private variables in events

Until now you had to override `serialize()` and `unserialize()` methods if you added any private members to your event classes even if you extended `EventBase`.
Serialization now uses reflection which might have small performance drawback but it is quite handy. So if you extend EventBase, you can forget this problem.

#### Closures are supported

Useful in unit tests or in simple cases.

```php
$closure = function (Event $event) {
    echo 'will be called with all events';
};
$bus->registerClosure($closure);
$bus->post(new SimpleEvent());
