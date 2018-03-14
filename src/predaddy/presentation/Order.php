<?php
declare(strict_types=1);

namespace predaddy\presentation;

use precore\lang\BaseObject;
use precore\lang\ObjectInterface;
use precore\util\Objects;

/**
 * Represents an ordering for the given property with the given direction.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class Order extends BaseObject
{
    /**
     * @var Direction
     */
    private $direction;

    /**
     * @var string
     */
    private $property;

    /**
     * @param Direction $direction
     * @param string $property
     */
    public function __construct(Direction $direction, string $property)
    {
        $this->direction = $direction;
        $this->property = $property;
    }

    /**
     * @return Direction
     */
    public function getDirection() : Direction
    {
        return $this->direction;
    }

    /**
     * @return string
     */
    public function getProperty() : string
    {
        return $this->property;
    }

    public function equals(ObjectInterface $object = null) : bool
    {
        if ($object === $this) {
            return true;
        }
        /* @var $object Order */
        return $object !== null
            && $this->getClassName() === $object->getClassName()
            && $this->direction->equals($object->direction)
            && $this->property === $object->property;
    }

    public function toString() : string
    {
        return Objects::toStringHelper($this)
            ->add($this->property)
            ->add($this->direction)
            ->toString();
    }
}
