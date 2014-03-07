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
use predaddy\messagehandling\MessageBus;
use predaddy\messagehandling\MessageBusFactory;
use predaddy\messagehandling\MessageHandlerDescriptorFactory;
use predaddy\messagehandling\SimpleMessageBus;
use predaddy\messagehandling\SimpleMessageBusFactory;

/**
 * The responsibility of this class is to
 *  - obtain the appropriate aggregate from its repository
 *  - pass the incoming commands to the aggregate
 *  - save it through the repository
 *
 * It catches only unhandled commands.
 *
 * @package predaddy\commandhandling
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class DirectCommandForwarder extends Object
{
    /**
     * @var RepositoryRepository
     */
    private $repositoryRepository;

    /**
     * @var MessageBusFactory
     */
    private $messageBusFactory;

    /**
     * @param RepositoryRepository $repositoryRepository
     * @param MessageBusFactory $messageBusFactory
     */
    public function __construct(
        RepositoryRepository $repositoryRepository,
        MessageBusFactory $messageBusFactory
    ) {
        $this->repositoryRepository = $repositoryRepository;
        $this->messageBusFactory = $messageBusFactory;
    }

    /**
     * @Subscribe
     * @param DeadMessage $deadMessage
     */
    public function catchDeadCommand(DeadMessage $deadMessage)
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
        $forwarderBus = $this->messageBusFactory->createBus($class->getName());
        $forwarderBus->register($aggregate);
        $forwarderBus->post($command);
        $repository->save($aggregate, $command->getVersion());
        self::getLogger()->info("Command [{}] has been applied", array($command));
    }
}
