<?php
declare(strict_types=1);

namespace predaddy\domain\eventsourcing;

use precore\util\UUID;
use predaddy\domain\AggregateId;
use predaddy\domain\DecrementedEvent;
use predaddy\domain\IncrementedEvent;
use predaddy\domain\UserCreated;
use predaddy\messagehandling\annotation\Subscribe;

/**
 * Description of User
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class EventSourcedUser extends AbstractEventSourcedAggregateRoot
{
    const DEFAULT_VALUE = 1;

    private $id;

    /**
     * @var int
     */
    public $value = self::DEFAULT_VALUE;

    /**
     * @Subscribe
     * @param CreateEventSourcedUser $command
     */
    public function __construct(CreateEventSourcedUser $command)
    {
        $this->apply(new UserCreated(EventSourcedUserId::create()));
    }

    /**
     * @return AggregateId
     */
    public function getId() : AggregateId
    {
        return $this->id;
    }

    /**
     * @Subscribe
     * @param Increment $command
     * @return int new value
     */
    public function increment(Increment $command)
    {
        $this->apply(new IncrementedEvent($this->id));
        return $this->value;
    }

    /**
     * @Subscribe
     * @param Decrement $command
     */
    public function decrement(Decrement $command)
    {
        $this->apply(new DecrementedEvent($this->value - 1));
    }

    /**
     * @Subscribe
     */
    private function handleCreated(UserCreated $event)
    {
        $this->id = $event->getUserId();
    }

    /**
     * @Subscribe
     */
    private function handleIncrementedEvent(IncrementedEvent $event)
    {
        $this->value++;
    }

    /**
     * @Subscribe
     * @param DecrementedEvent $event
     */
    private function handleDecrementedEvent(DecrementedEvent $event)
    {
        $this->value--;
    }
}
