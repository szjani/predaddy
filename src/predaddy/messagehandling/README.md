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
    )
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

In some cases handlers have return value or it is more common that they throw exceptions. In order to be notified about
these things it is possible to pass a second argument, a `MessageCallback` object to the `MessageBus::post()` method. The appropriate
method on this object will be called if any of the above happens.

```php
$bus->post(new SampleMessage1(), $messageCallback);
```

To ease of create such a `MessageCallback` object, `MessageCallbackClosures` provides a simple and powerful way to build a callback
object based on Closures.

```php
$messageCallback = MessageCallbackClosures::builder()
    ->successClosure($callback1)
    ->failureClosure($callback2)
    ->build();
```

### Handler prioritization

In predaddy 2.1 it is possible to define the sequence how handlers are being executed. The higher the priority the handler is being called earlier.
The default priority is 1.

For closures you can pass the priority to the `registerClosure` method as the second argument.

```php
$bus->registerClosure($closure1, -3);
$bus->registerClosure($closure2, 5);
```

In this case `$closure2` has higher priority so it will be called for the first time rather than `$closure1`.

For handler objects - supposed you are using annotation based configuration - you can define the priority as well.

```php
class PrioritizedHandler
{
    /**
     * @Subscribe
     */
    public function handler1(Message $msg)
    {
    }

    /**
     * @Subscribe(priority=-3)
     */
    public function handler2(Message $msg)
    {
    }

    /**
     * @Subscribe(priority=2)
     */
    public function handler3(Message $msg)
    {
    }
}
```

In this example the methods are being called in the following sequence: `handler3`, `handler1`, `handler2`.

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

There are three builtin interceptors:

 - `WrapInTransactionInterceptor`: All message dispatch processes will be wrapped in a new transaction.
 - `TransactionSynchronizedBuffererInterceptor`: Message dispatching is synchronized to transactions which means that if there is an already started transaction
 then messages are being buffered until the transaction is being committed. If the transaction is not successful then buffer is being cleared after rollback without sending out any messages.
 Without an already started transaction buffering is disabled.
 - `MonitoringInterceptor`: Catches all exceptions thrown by the message handlers and sends an `ErrorMessage` to the given `MessageBus` which contains both the exception and the original message.
 You can create a monitoring tool if you persist these errors and you have the chance to easily re-post the messages after you have fixed a bug in your application.


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

## Best practises

### Logging - lf4php

Predaddy uses [lf4php](https://github.com/szjani/lf4php) for logging. If you do not load an lf4php implementation, predaddy
will not log anything. Since lf4php is just an API, it is your responsibility to load and configure your preferred logging framework
and the corresponding lf4php binder. For more information see [lf4php documentation](https://github.com/szjani/lf4php).

### Overriding __toString for logging

Predaddy logs on different levels. In order to ease finding errors and debug application overriding `__toString()` method in messages is essential.
`AbstractMessage` overrides `toString()` inherited from `Object` which is part of precore library and string generation based on `ToStringHelper`.
If you extend `AbstractMessage` you can can easily add more fields into the generated string if you override `toStringHelper()` method.
The following example can be found in `AbstractCommand`:

```php
protected function toStringHelper()
{
    return parent::toStringHelper()
        ->add('aggregateId', $this->aggregateId)
        ->add('version', $this->version);
}
```

### Immutability

Although any objects can be send to a message bus, using immutable objects are usually recommended. It can be achieved
if all necessary dependencies are passed due the constructor and inner objects can be read only.

### Performance

If you use annotation based configuration which is the only way supported by predaddy, you should pay attention to annotation caching.
Annotation scanning takes time which can be avoided by using an explicitly set `Reader` object. The following example shows
how annotations can be cached in APC. You can read more about it in [Doctrine Annotation manual](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations.html#setup-and-configuration).

```php
$bus = new SimpleMessageBus(
    new AnnotatedMessageHandlerDescriptorFactory(
        new DefaultFunctionDescriptorFactory(),
        new CachedReader(new AnnotationReader(), new ApcCache())
    ),
);
```