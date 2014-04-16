predaddy
========
[![Latest Stable Version](https://poser.pugx.org/predaddy/predaddy/v/stable.png)](https://packagist.org/packages/predaddy/predaddy)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/szjani/predaddy/badges/quality-score.png?s=496589a983254d22b4334552572b833061b9bd03)](https://scrutinizer-ci.com/g/szjani/predaddy/)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/ad36fc7a-f48d-4919-b20d-90eae34aecd9/mini.png)](https://insight.sensiolabs.com/projects/ad36fc7a-f48d-4919-b20d-90eae34aecd9)
[![Gitter chat](https://badges.gitter.im/szjani/predaddy.png)](https://gitter.im/szjani/predaddy)

|master|1.2|2.1|2.2|
|------|---|---|---|
|[![Build Status](https://travis-ci.org/szjani/predaddy.png?branch=master)](https://travis-ci.org/szjani/predaddy)|[![Build Status](https://travis-ci.org/szjani/predaddy.png?branch=1.2)](https://travis-ci.org/szjani/predaddy)| [![Build Status](https://travis-ci.org/szjani/predaddy.png?branch=2.1)](https://travis-ci.org/szjani/predaddy)| [![Build Status](https://travis-ci.org/szjani/predaddy.png?branch=2.2)](https://travis-ci.org/szjani/predaddy)|
|[![Coverage Status](https://coveralls.io/repos/szjani/predaddy/badge.png?branch=master)](https://coveralls.io/r/szjani/predaddy?branch=master)|[![Coverage Status](https://coveralls.io/repos/szjani/predaddy/badge.png?branch=1.2)](https://coveralls.io/r/szjani/predaddy?branch=1.2)|[![Coverage Status](https://coveralls.io/repos/szjani/predaddy/badge.png?branch=2.0)](https://coveralls.io/r/szjani/predaddy?branch=2.1)|[![Coverage Status](https://coveralls.io/repos/szjani/predaddy/badge.png?branch=2.1)](https://coveralls.io/r/szjani/predaddy?branch=2.2)|

It is a library which gives you some usable classes to be able to use common DDD patterns. Some predaddy components can be used in any projects regardless of the fact that you are using DDD or not.
I have got several ideas from [Google's Guava EventBus](http://code.google.com/p/guava-libraries/wiki/EventBusExplained) and [Axon framework](http://www.axonframework.org/).

Some libraries are used which are just API libraries and you must care for their implementations:

1. [lf4php](https://github.com/szjani/lf4php) for logging. Without an implementation predaddy is not logging.
2. [trf4php](https://github.com/szjani/trf4php) for transaction management. In most cases you will need to use a trf4php implementation (eg. [trf4php-doctrine](https://github.com/szjani/trf4php-doctrine))

Components
----------

For more details see the components:

1. #### [Message handling](https://github.com/szjani/predaddy/tree/2.2/src/predaddy/messagehandling#messagebus)

   It's an annotation based publish/subscribe implementation, can be used any projects even without DDD/CQRS/Event Sourcing.

2. #### [CQRS and Event Sourcing](https://github.com/szjani/predaddy/tree/2.2/src/predaddy/domain#cqrs--event-sourcing)

   Complex solution for handling aggregates, based on the message handling component.

3. #### [Presentation - finders, etc.](https://github.com/szjani/predaddy/tree/2.2/src/predaddy/presentation#paginator-components)

   Common classes and interfaces for handling the read side. It also can be used in any applications.

Examples
--------

You can find some examples in the [sample directory](https://github.com/szjani/predaddy/tree/2.2/tests/src/sample).

A sample project is also available which shows how predaddy should be configured and used: https://github.com/szjani/predaddy-issuetracker-sample

History
-------

### 2.2
 - Explicit locking is optional. If the version in a `Command` instance is null, no explicit locking is going to be triggered. Useful for background, serialized jobs.
 - `DoctrineAggregateRootRepository` supports unversioned entities.
 - If a domain event extends `AbstractDomainEvent` and `DoctrineAggregateRootRepository` or `EventSourcingRepository` is being used,
   $aggregateId and $version parameters can be omitted during object construction in ARs. Both repository implementations automatically
   set these values.
 - `Versionable` interface can be implemented in domain objects. It can be useful in repositories.
 - `MessageCallbackClosures` class added to easily create a `MessageCallback` object.

### 2.1
 - [Prioritized handlers](https://github.com/szjani/predaddy/tree/2.1/src/predaddy/messagehandling#handler-prioritization) -
   If you have a direct `MessageBus` implementation, or your class extends `SimpleMessageBus` and overrides `registerClosure` method, you have to add a second, optional parameter. Some related changes are also made around inside classes.
 - `DoctrineAggregateRootRepository` can be a base class of your repositories in case of you are using Doctrine and DDD/CQRS.

### 2.0

 - Messages can be any objects, it's not required to implement `Message` interface.
 - The constructors are changed in all message bus implementations since they use the same `FunctionDescriptorFactory` for closures as the `MessageHandlerDescriptorFactory` for methods.
 - `TransactionSynchronizedBuffererInterceptor` has been introduced thus `EventBus` does not extends `Mf4PhpMessageBus` anymore.
 - Both `EventBus` and `CommandBus` now register their default interceptors in the constructor and do not override the `setInterceptors` method. If you want to register other interceptors,
 it's your responsibility to register all default interceptors if required.
 - Abstract message classes have been renamed and use `Abstract` prefix instead of `Base` suffix (eg. `EventBase` -> `AbstractEvent`).
 - Event Sourcing support with a built-in Doctrine implementation of `EventStore`.
 - Commands can be passed directly to the aggregate roots with `DirectCommandBus`.
 - Event and aggregate serialization can be customized with any `Serializer` implementation.
 - Events are not posted to the `EventBus` from the aggregate, it is managed by the repository when you are saving the aggregate.

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
