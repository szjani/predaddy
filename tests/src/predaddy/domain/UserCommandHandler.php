<?php
declare(strict_types=1);

namespace predaddy\domain;

use predaddy\messagehandling\annotation\Subscribe;

/**
 * Class UserCommandHandler
 *
 * @package predaddy\domain
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class UserCommandHandler
{
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Subscribe
     * @param CreateUser $command
     */
    public function handleCreateUser(CreateUser $command)
    {
        $this->repository->save(new User());
    }

    /**
     * @Subscribe
     * @param Increment $command
     * @return int the new value
     */
    public function handleIncrement(Increment $command)
    {
        /* @var $user User */
        $user = $this->repository->load(UserId::from($command->aggregateId()));
        $user->increment();
        return $user->value;
    }
}
 