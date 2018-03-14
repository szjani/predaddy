<?php
declare(strict_types=1);

namespace predaddy\domain;

use precore\lang\ObjectInterface;
use precore\util\Preconditions;
use precore\util\UUID;
use predaddy\messagehandling\annotation\Subscribe;

/**
 * Description of User
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class User extends AbstractAggregateRoot
{
    const DEFAULT_VALUE = 1;

    /**
     * @var UUIDAggregateId
     */
    private $id;

    /**
     * @var int
     */
    public $value = self::DEFAULT_VALUE;

    /**
     * @var int
     */
    private $version = 0;

    public function __construct()
    {
        $this->id = UUID::randomUUID()->toString();
        $this->raise(new UserCreated());
    }

    public function getId() : AggregateId
    {
        return new GenericAggregateId($this->id, self::className());
    }

    public function increment()
    {
        Preconditions::checkState($this->value <= 2, "Cannot be incremented more, than twice");
        $this->value++;
        $this->version++;
        $this->raise(new IncrementedEvent());
    }
}
