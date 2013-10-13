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
use Exception;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\DefaultFunctionDescriptorFactory;
use predaddy\messagehandling\MessageBase;
use predaddy\messagehandling\mf4php\Mf4PhpMessageBus;
use trf4php\doctrine\DoctrineTransactionManager;
use trf4php\TransactionManager;

class UserRegistered extends MessageBase
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
$dispatcher = new TransactedMemoryMessageDispatcher($transactionManager);

// event bus initialization
$functionDescFactory = new DefaultFunctionDescriptorFactory();
$messageHandlerDescFactory = new AnnotatedMessageHandlerDescriptorFactory($functionDescFactory);
$bus = new Mf4PhpMessageBus('mf4php_message_bus', $messageHandlerDescFactory, $functionDescFactory, $dispatcher);
$bus->register(new EmailSender());


/**
 * Code from a service class.
 */

// use transaction
/* @var $transactionManager TransactionManager */
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
