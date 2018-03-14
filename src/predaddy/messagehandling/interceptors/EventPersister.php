<?php
declare(strict_types=1);

namespace predaddy\messagehandling\interceptors;

use precore\lang\BaseObject;
use precore\lang\ObjectClass;
use predaddy\domain\EventStore;
use predaddy\messagehandling\DispatchInterceptor;
use predaddy\messagehandling\InterceptorChain;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class EventPersister extends BaseObject implements DispatchInterceptor
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @param EventStore $eventStore
     */
    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function invoke($message, InterceptorChain $chain) : void
    {
        ObjectClass::forName('predaddy\domain\DomainEvent')->cast($message);
        $this->eventStore->persist($message);
        self::getLogger()->debug('Message [{}] has been persisted into the message store', [$message]);
        $chain->proceed();
    }
}
