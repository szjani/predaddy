MessageBus
==========

MessageBus provides a general interface for message handling. The basic concept is that message handlers can
be registered to the bus which forwards each incoming messages to the appropriate handler. Message handlers
can be either objects or closures.

SimpleMessageBus is a basic implementation of the MessageBus interface. Currently, all other MessageBus implementations extend this class.

If you use CQRS, then I highly recommend to use the pre-configured `EventBus` and `CommandBus` classes.

## Example

### Configuration

You can use annotations to define message handler methods with the following configuration.

```php
$bus = new SimpleMessageBus(
    new AnnotatedMessageHandlerDescriptorFactory(
        new DefaultFunctionDescriptorFactory()
    ),
);
```

### Message

Message classes don't have to extend any classes or implement any interfaces, so a message must be an instance of any class. However a `Message` interface is available which provides some useful methods, and the `AbstractMessage` class implements this interface. You can put any data into the messages, but it's highly recommended to use simple types and you should consider to create immutable message objects.

```php
class SampleMessage1 extends AbstractMessage
{
}
```

If any of your message classes cannot be cast to string which is necessary for logging, you should register an error handler which can be found in precore library:
```php
ErrorHandler::register();
```
Otherwise `E_RECOVERABLE_ERROR` error is being triggered. If all of your message classes define a `__toString` method (`AbstractMessage` does), it is not necessary.

### Handlers

Predaddy supports message handler objects and closures as well. Since it's not required to implement any interfaces or extend any classes, you can use any objects as message handler. Predaddy supports annotations to mark handler methods.

```php
class SampleMessageHandler
{
    /**
     * @Subscribe
     */
    public function handleOne(SampleMessage1 $message)
    {
        printf(
            "handleOne: Incoming message %s sent %s\n",
            $message->getMessageIdentifier(),
            $message->getTimestamp()->format('Y-m-d H:i:s')
        );
    }

    /**
     * @Subscribe
     */
    public function handleTwo(SampleMessage2 $message)
    {
        printf(
            "handleTwo: Incoming message %s sent %s\n",
            $message->getMessageIdentifier(),
            $message->getTimestamp()->format('Y-m-d H:i:s')
        );
    }
}
```

```php
$closure = function (Message $message) {
    echo 'a message has been caught';
};
```

### Registering handlers

```php
$bus->register(new SampleMessageHandler());
$bus->registerClosure($closure);
```

### Sending messages

```php
$bus->post(new SampleMessage1());
```

## More details

### Handler methods/functions

Predaddy is quite configurable, but it has several default behaviours. Handler functions/methods must have one parameter with typehint.
The typehint defines which message objects can be handled, by default. If you want to handle for example all `Message` objects, which implement this interface,
you just have to use `Message` typehint. This kind of solution provides an easy way to use and distinguish a huge amount of
message classes. Interface and abstract class typehints also work as expected. A handler class could have as many handler methods as you want.
The handler methods must be public, but this rule can be overridden in the passed `MessageHandlerDescriptor`.

### Annotations

You can use your own handler method scanning/defining process, however the system does support annotation based configuration.
It means that you just have to mark handler methods in your handler classes with `@Subject` annotation. When you register an instance
of this class, predaddy is automatically finding these methods.

Do not forget to put the following use statement into your message handler classes:
```php
use predaddy\messagehandling\annotation\Subscribe;
```

### Interceptors

It's possible to extend bus behaviour when messages are being dispatched to message handlers. `HandlerInterceptor` objects wrap
the concrete dispatch process and are able to modify that. It is usable for logging, transactions, etc.

There are two builtin interceptors:

 - `WrapInTransactionInterceptor`: All message dispatch processes will be wrapped in a new transaction.
 - `TransactionSynchronizedBuffererInterceptor`: Message dispatching is synchronized to transactions which means that if there is an already started transaction
 then messages are being buffered until the transaction is being committed. If the transaction is not successful then buffer is being cleared after rollback without sending out any messages.
 Without an already started transaction buffering is disabled.

Both interceptors above use [trf4php](https://github.com/szjani/trf4php). If you want to use them, you will need a trf4php implementation (eg. [tf4php-doctrine](https://github.com/szjani/trf4php-doctrine)).

### Unhandled messages

Those messages which haven't been sent to any handlers during the dispatch process are being wrapped into an individual `DeadMessage` object and being dispatched again.
Registering a `DeadMessage` handler is a common way to log or debug unhandled messages.

### MessageBus implementations

There is a default implementation of `MessageBus` interface called `SimpleMessageBus`. Predaddy also provides some other specialized implementations which extend this class.

#### CommandBus

`WrapInTransactionInterceptor` is registered which indicates that all command handlers are wrapped in a unique transaction.
`Message` objects must implement `Command` interface. The typehint in the handler methods must be exactly the same as the command object's type.

#### DirectCommandBus

`DirectCommandBus` extends `CommandBus` and automatically registers a `DirectCommandForwarder` object as a handler which handles all unhandled commands. This bus should be used if business method parameters in the aggregates are `Command` objects.

#### EventBus

`TransactionSynchronizedBuffererInterceptor` is registered which means that event handlers are being called only after a the transaction has been successfully committed (messages are buffered).
This message bus implementation uses the default typehint handling (subclass handling, etc.). Message objects
must implement `Event` interface.

#### Mf4phpMessageBus

`Mf4phpMessageBus` wraps a `MessageDispatcher`, so all features provided by [mf4php](https://github.com/szjani/mf4php) can be achieved with this class, such as
synchronize messages to transactions and asynchronous event dispatching. For further information see the [mf4php](https://github.com/szjani/mf4php) documentation.
