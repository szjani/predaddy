<?php
declare(strict_types=1);

namespace sample;

require_once __DIR__ . '/../../bootstrap.php';

use predaddy\messagehandling\annotation\Subscribe;
use predaddy\messagehandling\AbstractMessage;
use predaddy\messagehandling\SimpleMessageBus;

class UserRegistered extends AbstractMessage
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
$bus = new SimpleMessageBus();
$bus->register(new EmailSender());

// post some events
$bus->post(new UserRegistered('example1@example.com'));
$bus->post(new UserRegistered('example2@example.com'));
