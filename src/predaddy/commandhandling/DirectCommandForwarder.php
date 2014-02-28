<?php
/*
 * Copyright (c) 2012-2014 Szurovecz János
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

namespace predaddy\commandhandling;

use precore\lang\Object;
use precore\lang\ObjectClass;
use predaddy\domain\RepositoryRepository;
use predaddy\messagehandling\annotation\Subscribe;
use predaddy\messagehandling\DeadMessage;
use predaddy\messagehandling\MessageHandlerDescriptorFactory;
use predaddy\messagehandling\SimpleMessageBus;

class DirectCommandForwarder extends Object
{
    /**
     * @var RepositoryRepository
     */
    private $repositoryRepository;

    /**
     * @var MessageHandlerDescriptorFactory
     */
    private $descFact;

    /**
     * @param RepositoryRepository $repositoryRepository
     * @param MessageHandlerDescriptorFactory $descFact Should be the same as used in command bus
     */
    public function __construct(RepositoryRepository $repositoryRepository, MessageHandlerDescriptorFactory $descFact)
    {
        $this->repositoryRepository = $repositoryRepository;
        $this->descFact = $descFact;
    }

    /**
     * Workaround to be able to catch any commands since typehint has to match with the incoming command.
     *
     * @Subscribe
     * @param DeadMessage $deadMessage
     */
    public function catchDeadCommands(DeadMessage $deadMessage)
    {
        $innerMessage = $deadMessage->getMessage();
        ObjectClass::forName(__NAMESPACE__ . '\DirectCommand')->cast($innerMessage);
        $this->forwardCommand($innerMessage);
    }

    /**
     * @param DirectCommand $command
     */
    protected function forwardCommand(DirectCommand $command)
    {
        $class = ObjectClass::forName($command->getAggregateClass());
        $repository = $this->repositoryRepository->getRepository($class);
        $aggregateId = $command->getAggregateId();
        if ($aggregateId === null) {
            $aggregate = $class->newInstanceWithoutConstructor();
        } else {
            $aggregate = $repository->load($aggregateId);
        }
        $forwarderBus = new SimpleMessageBus($this->descFact);
        $forwarderBus->register($aggregate);
        $forwarderBus->post($command);
        $repository->save($aggregate, $command->getVersion());
        self::getLogger()->info("Command [{}] has been applied", array($command));
    }
}
