<?php
namespace sample;

/**
 * This example cannot be executed directly, you have to provide its dependencies!
 *
 * @see https://github.com/szjani/trf4php-doctrine
 * @see https://github.com/doctrine/doctrine2
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Exception;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\event\EventBase;
use predaddy\messagehandling\event\EventBus;
use predaddy\messagehandling\event\EventFunctionDescriptorFactory;
use predaddy\messagehandling\MessageBase;
use trf4php\doctrine\DoctrineTransactionManager;
use trf4php\TransactionManager;

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

class EmailSender
{
    /**
     * @Subscribe
     */
    public function sendMail(UserRegistered $message)
    {
        printf("Sending email to %s...\n", $message->getEmail());
    }
}

/**
 * Initialization
 */

/* @var $em \Doctrine\ORM\EntityManager */
$transactionManager = new DoctrineTransactionManager($em);

// event bus initialization
$messageHandlerDescFactory = new AnnotatedMessageHandlerDescriptorFactory(new EventFunctionDescriptorFactory());
$eventBus = new EventBus($messageHandlerDescFactory, $transactionManager);
$eventBus->register(new EmailSender());


/**
 * Code from a service class.
 */

// use transaction
/* @var $transactionManager TransactionManager */
$transactionManager->beginTransaction();
try {
    // database modifications ...
    $eventBus->post(new UserRegistered('example1@example.com'));
    $transactionManager->commit();
    // EmailSender::sendMail will be called at this point
} catch (Exception $e) {
    $transactionManager->rollback();
    // EmailSender::sendMail won't be called
}
