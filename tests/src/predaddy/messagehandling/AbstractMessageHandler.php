<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use precore\lang\BaseObject;
use precore\util\UUID;
use predaddy\messagehandling\annotation\Subscribe;

/**
 * Description of AbstractMessageHandler
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class AbstractMessageHandler extends BaseObject
{
    /**
     * @var Message
     */
    public $lastMessage;

    /**
     * @var UUID
     */
    public $lastParentMessage;

    public $counterInParent = 0;

    /**
     * @Subscribe
     * @param UUID $message
     */
    public function handleInParent(UUID $message)
    {
        $this->lastParentMessage = $message;
        $this->counterInParent++;
    }
}
