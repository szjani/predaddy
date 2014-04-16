<?php
/*
 * Copyright (c) 2013 Szurovecz János
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace predaddy\domain;

use precore\util\UUID;
use predaddy\messagehandling\annotation\Subscribe;

/**
 * Description of User
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class EventSourcedUser extends AbstractEventSourcedAggregateRoot
{
    const DEFAULT_VALUE = 1;

    private $id;
    public $value = self::DEFAULT_VALUE;

    /**
     * @Subscribe
     * @param CreateEventSourcedUser $command
     */
    public function __construct(CreateEventSourcedUser $command)
    {
        $this->apply(new UserCreated(new UUIDAggregateId(UUID::randomUUID()), $command->getVersion()));
    }

    /**
     * @return AggregateId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @Subscribe
     * @param Increment $command
     */
    public function increment(Increment $command)
    {
        $this->apply(new IncrementedEvent($this->id, $command->getVersion()));
    }

    /**
     * @Subscribe
     * @param Decrement $command
     */
    public function decrement(Decrement $command)
    {
        $this->apply(new DecrementedEvent());
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
