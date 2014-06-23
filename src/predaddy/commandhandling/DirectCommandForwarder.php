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

use Exception;
use precore\lang\Object;
use precore\lang\ObjectClass;
use predaddy\domain\RepositoryRepository;
use predaddy\domain\StateHashAwareAggregateRoot;
use predaddy\messagehandling\annotation\Subscribe;
use predaddy\messagehandling\DeadMessage;
use predaddy\messagehandling\MessageBusFactory;
use predaddy\messagehandling\util\MessageCallbackClosures;

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
     * @return mixed The return value of the last actual handler
     */
    public function catchDeadCommand(DeadMessage $deadMessage)
    {
        $innerMessage = $deadMessage->getMessage();
        ObjectClass::forName(__NAMESPACE__ . '\DirectCommand')->cast($innerMessage);
        return $this->forwardCommand($innerMessage);
    }

    /**
     * @param DirectCommand $command
     * @throws \Exception If the handler throws any
     * @return mixed The return value of the last handler (should be one handler per aggregate)
     */
    protected function forwardCommand(DirectCommand $command)
    {
        $class = $command->getAggregateClass();
        $repository = $this->repositoryRepository->getRepository($class);
        $aggregateId = $command->getAggregateId();
        if ($aggregateId === null) {
            $aggregate = ObjectClass::forName($class)->newInstanceWithoutConstructor();
        } else {
            $aggregate = $repository->load($aggregateId);
            if ($aggregate instanceof StateHashAwareAggregateRoot && $command->getStateHash() !== null) {
                $aggregate->failWhenStateHashViolation($command->getStateHash());
            }
        }
        $forwarderBus = $this->messageBusFactory->createBus($class);
        $forwarderBus->register($aggregate);
        $result = null;
        $thrownException = null;
        $callback = MessageCallbackClosures::builder()
            ->successClosure(
                function ($res) use (&$result) {
                    $result = $res;
                }
            )
            ->failureClosure(
                function (Exception $exp) use (&$thrownException) {
                    $thrownException = $exp;
                }
            )
            ->build();
        $forwarderBus->post($command, $callback);
        if ($thrownException instanceof Exception) {
            throw $thrownException;
        }
        $repository->save($aggregate);
        self::getLogger()->info("Command [{}] has been applied", [$command]);
        return $result;
    }
}
