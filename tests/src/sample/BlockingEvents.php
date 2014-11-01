<?php
namespace sample;

require_once __DIR__ . '/../../bootstrap.php';

use Exception;
use predaddy\commandhandling\AbstractCommand;
use predaddy\commandhandling\CommandBus;
use predaddy\eventhandling\Event;
use predaddy\eventhandling\EventBus;
use predaddy\messagehandling\AbstractMessage;
use predaddy\messagehandling\interceptors\BlockerInterceptor;
use predaddy\messagehandling\annotation\Subscribe;

class RegisterUsers extends AbstractCommand
{
}

class UserRegistered extends AbstractMessage implements Event
{
    protected $email;

    public function __construct($email)
    {
        parent::__construct();
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }
}

class EmailSender
{
    /**
     * @Subscribe
     */
    public function sendMail(UserRegistered $message)
    {
        printf("============= Sending email to %s... =============\n", $message->getEmail());
    }
}

/**
 * Initialization
 */

// event bus initialization
$blockerInterceptor = new BlockerInterceptor();
$eventBus = EventBus::builder()
    ->withInterceptors([$blockerInterceptor])
    ->build();
$eventBus->register(new EmailSender());

// command bus initialization
$commandBus = CommandBus::builder()
    ->withInterceptors([$blockerInterceptor->manager()])
    ->withExceptionHandler($blockerInterceptor->manager())
    ->build();

/**
 * Explicit handling of $blockerInterceptor, no command bus.
 */
$blockerInterceptor->startBlocking();
try {
    $eventBus->post(new UserRegistered('example1@example.com'));
    $eventBus->post(new UserRegistered('example2@example.com'));
    throw new Exception('Database connection error');
} catch (Exception $e) {
    $blockerInterceptor->clear();
}
$blockerInterceptor->flush();
$blockerInterceptor->stopBlocking();

/**
 * The same as above, but implicit handling of $blockerInterceptor.
 * Using command bus.
 */
$commandBus->registerClosure(
    function (RegisterUsers $command) use ($eventBus) {
        $eventBus->post(new UserRegistered('example3@example.com'));
        $eventBus->post(new UserRegistered('example4@example.com'));
        throw new Exception('Database connection error');
    }
);
$commandBus->post(new RegisterUsers());
