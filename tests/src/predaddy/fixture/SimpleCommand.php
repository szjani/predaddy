<?php

namespace predaddy\fixture;

use JMS\Serializer\Annotation\Type;
use predaddy\commandhandling\AbstractCommand;

/**
 * Class SimpleCommand
 *
 * @package predaddy\fixture
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class SimpleCommand extends AbstractCommand
{
    /**
     * @Type("string")
     */
    private $arg1;

    /**
     * @Type("string")
     */
    private $arg2;

    public function __construct($aggregateId, $arg1, $arg2)
    {
        parent::__construct($aggregateId);
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }

    /**
     * @return mixed
     */
    public function getArg1()
    {
        return $this->arg1;
    }

    /**
     * @return mixed
     */
    public function getArg2()
    {
        return $this->arg2;
    }
}
