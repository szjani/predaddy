predaddy
========

It is a library which gives you some usable classes to be able to use common DDD patterns.
You can find some examples in the [sample directory](https://github.com/szjani/predaddy/tree/master/sample).

EventBus
--------

Domain (and other) events usually used in DDD project to achieve loose coupling between objects. There are several solutions
available to use publish-subscribe pattern in PHP, however most of them (at least as I know) are hard to be extended and
they do not pay attention to transactions.

The general way to handle a common user interaction (focus only to the backend part):

* Call service method
* Start a transaction
* Do the expected behaviors based on the business logic
* Commit the transaction

In this workflow, domain events are coming from the domain models, which is inside the transaction. If we handle those events
immediately, the handling process also will be executed in that particular transaction. Instead, event handling process
must be executed if and only if the transaction has been successfully committed. This behavior is achievable with the event bus
available in this package.

### Basic usage of EventBus

If you want to use it in your aggregate roots, a possible solution is extending the `AggregateRoot` class and pass the
instantiated `EventBus` object to the `AggregateRoot` class in a static way. In your aggregate roots you can call the `raise()` method
with an appropriate `DomainEvent` object.

Event objects must implement the `Event` interface. `EventBase` is useful in most cases and for domain events, extending `DomainEvent`
class is mandatory.

Event handlers are really easy to use. Predaddy scans the registered objects and tries to find the event handler methods.
There are three rules:

* Handler methods must be public
* They have to have exactly one parameter, which implements `Event` interface
* Must be marked with `@Subscribe` annotation

If a handler method can handle an instance of a particular event class, then that will be called with all events which
are compatible with the defined class. For instance using `Event` interface as a typehint in your handle method effects that all raised events will be passed to that method.

#### DirectEventBus

This EventBus implementation calls directly the handle methods. Useful in tests or if you want to handle events immediately they raised.

#### Mf4PhpEventBus

mf4php is a messaging library which can be used to send events. This implementation uses an mf4php queue and puts all
events into that. The bus itself is a `MessageListener`, so all messages sent to that queue will be passed to it. With this
solution, all features shipped with mf4php can be used like transaction listening or asynchronous message handling
(see Beanstalk mf4php implementation). If you want to delay some events, you can register `ObjectMessageFactory` object
which can create the appropriate `DelayableMessage` instances for the defined event class.

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
$page = $userFinder->getPage(Pageable $pageable);
```

In the above example the `$page` object stores the users and a lot of information to be able to create a paginator on the UI.

History
-------

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
