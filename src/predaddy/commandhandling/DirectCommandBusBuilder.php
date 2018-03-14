<?php
declare(strict_types=1);

namespace predaddy\commandhandling;

use predaddy\domain\Repository;

/**
 * Builder for {@link DirectCommandBus}.
 *
 * @package predaddy\commandhandling
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class DirectCommandBusBuilder extends CommandBusBuilder
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * @return Repository
     */
    public function getRepository() : Repository
    {
        return $this->repository;
    }

    /**
     * @return DirectCommandBus
     */
    public function build()
    {
        return new DirectCommandBus($this);
    }
}
