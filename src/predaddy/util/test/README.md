Testing
-------

This package is under development heavily, which means bigger modifications can be done in the future.
The aim is to ease aggregate testing. Since all test scenarios follow the same pattern, it reduces the work you need to do.

The following steps must be done in most cases:

1. **Initializing the aggregate**
This step depends on the aggregate type. If it is an event sourced aggregate, we can replay some events on the aggregate
to bring that into the expected state. Otherwise it can be achieved by sending some init commands.

2. **Sending a command**
A command must be sent to the bus in order to check how the aggregate state will be modified.

3. **Validation**
There are some possible result of a processed command. Some domain events can be raised, perhaps the command handler throws
and exception, and/or it has a return value.

All of the above things can be defined with `Fixtures`.

Let's see an example:

```php
$fixture = Fixtures::newGivenWhenThenFixture(Account::className());
$fixture
   ->givenEvents(
      new AccountCreated(),
      new EntryAdded(100),
      new EntryAdded(200),
   )
   ->when(new AddEntry(300))
   ->expectReturnValue(600)
   ->expectEvents(new EntryAdded(300));
```

It is a simple bank account example, which stores the sum of all entries. The command handler method returns the actual balance, which is 600 in this case, and an `EntryAdded` event is expected to be raised.

Another example, where we need to check an exception:

```php
$fixture = Fixtures::newGivenWhenThenFixture(Account::className());
$fixture
   ->givenEvents(
      new AccountCreated(),
      new EntryAdded(100)
   )
   ->when(new AddEntry(-300))
   ->expectException('\RuntimeException', 'Balance cannot be negative, entry is not allowed!');
```

If we have separated command handler, it can be registered as well. Using it with command initialization (non-ES):

```php
$fixture = Fixtures::newGivenWhenThenFixture(Account::className());
$fixture
   ->registerAnnotatedCommandHandler(new AccountCommandHandler(new InMemoryRepository()))
   ->givenCommands(
      new CreateAccount(),
      new AddEntry(100)
   )
   ->...
```