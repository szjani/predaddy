<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use precore\util\ToStringHelper;

/**
 * Wraps a message which has not been handled by handlers.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class DeadMessage extends AbstractMessage
{
    private $message;

    /**
     * @param $message
     */
    public function __construct($message)
    {
        parent::__construct();
        $this->message = $message;
    }

    /**
     * @return object
     */
    public function wrappedMessage()
    {
        return $this->message;
    }

    protected function toStringHelper() : ToStringHelper
    {
        return parent::toStringHelper()->add('message', $this->message);
    }
}
