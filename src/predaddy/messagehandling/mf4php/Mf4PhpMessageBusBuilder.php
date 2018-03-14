<?php
declare(strict_types=1);

namespace predaddy\messagehandling\mf4php;

use mf4php\MessageDispatcher;
use predaddy\messagehandling\SimpleMessageBusBuilder;

/**
 * Builder for {@link Mf4PhpMessageBus}.
 *
 * @package predaddy\messagehandling\mf4php
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class Mf4PhpMessageBusBuilder extends SimpleMessageBusBuilder
{
    const DEFAULT_NAME = 'mf4php-bus';

    /**
     * @var MessageDispatcher
     */
    private $dispatcher;

    /**
     * @param MessageDispatcher $dispatcher
     */
    public function __construct(MessageDispatcher $dispatcher)
    {
        parent::__construct();
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return string
     */
    protected static function defaultName() : string
    {
        return self::DEFAULT_NAME;
    }

    /**
     * @return MessageDispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @return Mf4PhpMessageBus
     */
    public function build()
    {
        return new Mf4PhpMessageBus($this);
    }
}
