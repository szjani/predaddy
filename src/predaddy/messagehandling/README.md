MessageBus
==========

MessageBus provides a general interface for message handling. The basic concept is that message handlers can
be registered to the bus which forwards each incoming message to the appropriate handlers. Message handlers
can be either objects or closures.

`SimpleMessageBus` is a basic implementation of the `MessageBus` interface. Currently, all other `MessageBus` implementations extend this class.

If you use CQRS, then I highly recommend to use the pre-configured `EventBus` and `CommandBus` classes.

## Example

### Configuration

You can use annotations to define message handler methods with the following configuration.

```php
$bus = new SimpleMessageBus();
```

### Message

Message classes do not have to extend neither a class nor implement an interface, but messages must be objects. A `Message` interface is available, which provides some useful methods, and the `AbstractMessage` class implements this interface.

```php
class SampleMessage1 extends AbstractMessage
{
}
```

If a message class cannot be cast to string, which is necessary for logging, you should register an error handler that can be found in precore library:
```php
ErrorHandler::register();
```
Otherwise `E_RECOVERABLE_ERROR` error is being triggered. If all of your message classes define a `__toString` method (`AbstractMessage` does), it is not necessary.

### Handlers

Predaddy supports message handler objects and closures as well. Since it is not required to implement neither an interfaces nor extend a class, you can use a simple object as message handler. Predaddy supports annotations to recognize handler methods.

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
            $message->identifier(),
            $message->created()->format('Y-m-d H:i:s')
        );
    }

    /**
     * @Subscribe
     */
    public function handleTwo(SampleMessage2 $message)
    {
        printf(
            "handleTwo: Incoming message %s sent %s\n",
            $message->identifier(),
            $message->created()->format('Y-m-d H:i:s')
        );
    }
}
```

```php
$closure = function (Message $message) {
    echo 'a message has been caught';
};
```

If you prefer non-annotation based configuration, you can use `Configuration` and the related `ConfiguredMessageHandlerDescriptorFactory`
classes to setup a `SimpleMessageBus` object.

The following example shows how you can use both annotation scanning and configuration based setup at the same time.

```php
// handler method configuration
$configuration = Configuration::builder()
    ->withMethod('Foo\Foo', 'handlerMethodName')
    ->withMethod('Bar\Bar', 'barHandler', 3)
    ->withMethod('Foo\AbstractHandler', 'allMessageHandler')
    ->build();

$functionDescFactory = new DefaultFunctionDescriptorFactory();
$messageBus = SimpleMessageBus::builder()
    ->withHandlerDescriptorFactory(
        new MultipleMessageHandlerDescriptorFactory(
            $functionDescFactory,
            [
                new ConfiguredMessageHandlerDescriptorFactory($functionDescFactory, $configuration),
                new AnnotatedMessageHandlerDescriptorFactory($functionDescFactory)
            ]
        )
    )
    ->build();
);
```

Hint: Feel free to implement a configuration file reading utility class that provides the properly built `Configuration` object.

### Registering handlers

```php
$bus->register(new SampleMessageHandler());
$bus->registerClosure($closure);
```

### Sending messages

```php
$bus->post(new SampleMessage1());
```

All handlers that are registered in `$bus` and able to handle `SampleMessage1` message type will be called by the message bus.

In some cases handlers have return value or it is more common that they throw exception. In order to be notified about
these things it is possible to pass a second argument, a `MessageCallback` object to the `MessageBus::post()` method. The appropriate
method on this object will be called if any of the above happens.

```php
$bus->post(new SampleMessage1(), $messageCallback);
```

To ease of create such a `MessageCallback` object, you can use `SimpleMessageCallback`. `MessageCallbackClosures` provides a simple and powerful way to build a callback
object based on Closures:

```php
$messageCallback = MessageCallbackClosures::builder()
    ->successClosure($callback1)
    ->failureClosure($callback2)
    ->build();
```

## Dynamic handlers

Predaddy 3 supports dynamic handlers. In a typical application the PHP interpreter stops after the response has been sent out, therefore
it may be unnecessary to create and register in all handlers. Rather than, you can register handler factory closures which act as handler closures:
their parameter's typehint defines which type of object can be handled and if an object is compatible with that, the message bus
calls the factory function and passes the message to the return value just like it was registered as a handler.

This behavior is defined in `HandlerFactoryRegisterableMessageBus` which is implemented by `SimpleMessageBus`.

```php
$factory = function (Message $message) {
    return new SimpleMessageHandler();
};
$this->bus->registerHandlerFactory($factory);
```

If a message's type is compatible with the factory's typehint, the factory will be called and the handler will be constructed even if the handler will not be able to handle the message.

Hint: You can use your dependency injection container object within these factories to obtain and return the appropriate handler.

## Exception handling

All `MessageBus` implementation catches all exceptions that were thrown by handlers, so the messages are being forwarded
to all handlers.

If you need to handle exceptions in a generic way, you can implement `SubscriberExceptionHandler` interface and use builder to instantiate the bus:

```php
$bus = SimpleMessageBus::builder()
    ->withExceptionHandler($exceptionHandler)
    ->build();
```

## Handler prioritization

Since version 2.1 it is possible to define the sequence how handlers are being executed. The higher the priority the handler is being called earlier.
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
The typehint defines which message objects can be handled, by default. If you want to handle for example all objects that implement `Message` interface,
you just need to define one handler method with a `Message` parameter. This kind of solution provides an easy way to use and distinguish a huge amount of
message classes. Interface and abstract class typehints work as expected. A handler class could have as many handler methods as you want.
The handler methods must be public, but this rule can be overridden in the passed `MessageHandlerDescriptor`.

### Annotations

You can use your own handler method scanning/defining process, however the system does support annotation based configuration.
It means that you just have to mark handler methods in your handler classes with `@Subscribe` annotation. When you register an instance
of this class, predaddy is automatically finding these methods.

Do not forget to put the following use statement into your message handler classes:
```php
use predaddy\messagehandling\annotation\Subscribe;
```

### Interceptors

It is possible to extend bus behaviour when messages are being dispatched. `DispatchInterceptor` objects wrap
the concrete dispatch process and are able to modify that. It is usable for logging, transaction handling, etc.

The following interceptors are shipped with predaddy:

 - `WrapInTransactionInterceptor`: The message dispatch processes will be wrapped in a transaction. Needs [trf4php](https://github.com/szjani/trf4php).
 - `BlockerInterceptor`: Message dispatching can be blocked by this interceptor and all collected messages can be flushed or cleared.
 - `BlockerInterceptorManager`: It manages the given `BlockerInterceptor`. Should be used with another message bus object. Currently it is used
 by `CommandBus`: blocks all outgoing events until the command handler execution ends.
 - `EventPersister`: Persists all messages that go through the message bus into an `EventStore`.

In predaddy 3, interceptors are called only once for each message.

### Unhandled messages

Those messages that haven not been sent to any handlers during the dispatch process are being wrapped into an individual `DeadMessage` object and being dispatched again.
Registering a `DeadMessage` handler is a common way to log or debug unhandled messages.

### MessageBus implementations

There is a default implementation of `MessageBus` interface called `SimpleMessageBus`. Predaddy also provides some other specialized implementations which extend this class.

#### CommandBus

`Message` objects must implement `Command` interface. The typehint in the handler methods must be exactly the same as the command object's type. If more than one
handler can the command be passed to, `IllegalStateException` will be thrown.

#### DirectCommandBus

`DirectCommandBus` extends `CommandBus` and handles all unhandled commands. This bus should be used if business method parameters in the aggregates are mostly `Command` objects.

#### EventBus

Only `Event` objects will be passed to the appropriate handlers.

#### Mf4phpMessageBus

`Mf4phpMessageBus` wraps a `MessageDispatcher`, so all features provided by [mf4php](https://github.com/szjani/mf4php) can be achieved with this class, such as
synchronizing messages to transactions and asynchronous event dispatching. For further information see the [mf4php](https://github.com/szjani/mf4php) documentation.

## Best practises

### Logging - lf4php

Predaddy uses [lf4php](https://github.com/szjani/lf4php) for logging. If you do not load an lf4php implementation, predaddy
will not log anything. Since lf4php is just an API, it is your responsibility to load and configure your preferred logging framework
and the corresponding lf4php binder. For more information see [lf4php documentation](https://github.com/szjani/lf4php).

### Overriding __toString for logging

Predaddy logs on different levels. In order to ease finding errors and debug application overriding `__toString()` method in messages is essential.
`AbstractMessage` overrides `toString()` inherited from `Object`, which is part of precore library, and string generation is based on `ToStringHelper`.
If you extend `AbstractMessage` you can can easily add more fields into the generated string if you override `toStringHelper()` method.
The following example can be found in `AbstractDomainEvent`:

```php
protected function toStringHelper()
{
    return parent::toStringHelper()
        ->add($this->aggregateId())
        ->add('stateHash', $this->stateHash());
}
```

### Immutability

Although any objects can be send to a message bus, using immutable objects are usually recommended. It can be achieved
if all necessary dependencies are passed to the constructor and inner objects are read only.

### Performance

If you use annotation based configuration, you should pay attention to annotation caching.
Annotation scanning takes time which can be avoided by using an explicitly set `Reader` object. The following example shows
how annotations can be cached in APC. You can read more about it in [Doctrine Annotation manual](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations.html#setup-and-configuration).

```php
AnnotatedMessageHandlerDescriptor::setReader(new CachedReader(new AnnotationReader(), new ApcCache()));
```
