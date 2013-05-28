<?php
namespace sample;

require_once __DIR__ . '/../vendor/autoload.php';

use precore\lang\Object;
use predaddy\eventhandling\DirectEventBus;
use predaddy\eventhandling\EventBase;
use predaddy\eventhandling\EventHandler;
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
     */
    public function sendMail(UserRegistered $event)
    {
        printf("Sending email to %s...\n", $event->getEmail());
    }
}

// initialize event bus
$bus = new DirectEventBus('sample');
$bus->register(new EmailSender());

// post some events
$bus->post(new UserRegistered('example1@example.com'));
$bus->post(new UserRegistered('example2@example.com'));
