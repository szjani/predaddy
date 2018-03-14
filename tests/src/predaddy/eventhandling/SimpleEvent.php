<?php
declare(strict_types=1);

namespace predaddy\eventhandling;

use predaddy\messagehandling\AbstractMessage;

class SimpleEvent extends AbstractMessage implements Event
{
    public $content;
}
