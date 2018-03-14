<?php
declare(strict_types=1);

namespace predaddy\commandhandling;

use precore\lang\BaseObject;
use predaddy\domain\Repository;

/**
 * Basic command handler which provides you the ability to use the proper repository.
 * Should be used if explicit command handlers are used on a CommandBus.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class AbstractCommandHandler extends BaseObject
{
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return Repository
     */
    public function getRepository() : Repository
    {
        return $this->repository;
    }
}
