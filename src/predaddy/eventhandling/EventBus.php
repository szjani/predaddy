<?php
declare(strict_types=1);

namespace predaddy\eventhandling;

use predaddy\messagehandling\SimpleMessageBus;

/**
 * With default configuration it supports only handlers which can process {@link Event} objects.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class EventBus extends SimpleMessageBus
{
    /**
     * @param EventBusBuilder $builder
     */
    public function __construct(EventBusBuilder $builder = null)
    {
        if ($builder === null) {
            $builder = self::builder();
        }
        parent::__construct($builder);
    }

    /**
     * @return EventBusBuilder
     */
    public static function builder()
    {
        return new EventBusBuilder();
    }
}
