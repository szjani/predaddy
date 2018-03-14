<?php
declare(strict_types=1);

namespace predaddy\domain;

use precore\lang\BaseObject;
use precore\lang\ObjectInterface;
use precore\util\Objects;

/**
 * Basic implementation of AggregateId. Intended for internal use.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class GenericAggregateId extends BaseObject implements AggregateId
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $aggregateClass;

    /**
     * @param string $value
     * @param string $aggregateClass
     */
    public function __construct(string $value, string $aggregateClass)
    {
        $this->value = $value;
        $this->aggregateClass = $aggregateClass;
    }

    /**
     * @return string
     */
    public function value() : string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function aggregateClass() : string
    {
        return $this->aggregateClass;
    }

    final public function equals(ObjectInterface $object = null) : bool
    {
        if ($object === $this) {
            return true;
        }
        return $object instanceof AggregateId
            && $this->value() === $object->value()
            && $this->aggregateClass() === $object->aggregateClass();
    }

    final public function toString() : string
    {
        return Objects::toStringHelper($this)
            ->add($this->value())
            ->add($this->aggregateClass())
            ->toString();
    }
}
