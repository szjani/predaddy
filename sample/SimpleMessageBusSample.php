<?php
namespace sample;

require_once __DIR__ . '/../vendor/autoload.php';

use predaddy\messagehandling\annotation\Subscribe;
use predaddy\messagehandling\MessageBase;

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

// initialize event bus
$bus = require_once 'sampleBus.php';
$bus->register(new EmailSender());

// post some events
$bus->post(new UserRegistered('example1@example.com'));
$bus->post(new UserRegistered('example2@example.com'));
