<?php
namespace sample;

/**
 * This example cannot be executed directly, you have to provide its dependencies!
 *
 * @see https://github.com/szjani/trf4php-doctrine
 * @see https://github.com/doctrine/doctrine2
 */

require_once __DIR__ . '/../vendor/autoload.php';

use mf4php\memory\TransactedMemoryMessageDispatcher;
use precore\lang\Object;
use predaddy\eventhandling\EventBase;
use predaddy\eventhandling\EventHandler;
use predaddy\eventhandling\mf4php\Mf4PhpEventBus;
use trf4php\doctrine\DoctrineTransactionManager;
use Exception;
use predaddy\eventhandling\Subscribe;

class UserRegistered extends EventBase
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

class EmailSender extends Object implements EventHandler
{
    /**
     * @Subscribe
     * @param \sample\UserRegistered $event
     */
    public function sendMail(UserRegistered $event)
    {
        printf("Sending email to %s...\n", $event->getEmail());
    }
}

/**
 * Initialization
 */

/* @var $em \Doctrine\ORM\EntityManager */
$transactionManager = new DoctrineTransactionManager($em);
$dispatcher = new TransactedMemoryMessageDispatcher($transactionManager);

// event bus initialization
$bus = new Mf4PhpEventBus('mf4php_event_bus', $dispatcher);
$bus->register(new EmailSender());


/**
 * Code from a service class.
 */

// use transaction
$transactionManager->beginTransaction();
try {
    // database modifications ...
    $bus->post(new UserRegistered('example1@example.com'));
    $transactionManager->commit();
    // EmailSender::sendMail will be called at this point
} catch (Exception $e) {
    $transactionManager->rollback();
    // EmailSender::sendMail won't be called
}
