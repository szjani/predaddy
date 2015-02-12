<?php

namespace predaddy\domain;

use precore\lang\Object;
use precore\lang\ObjectInterface;
use precore\util\Objects;

/**
 * Can be used as parent of a class which implements {@link AggregateId}.
 *
 * @package predaddy\domain
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class AbstractAggregateId extends Object implements AggregateId
{
    final public function equals(ObjectInterface $object = null)
    {
        if ($object === $this) {
            return true;
        }
        /* @var $object AbstractAggregateId */
        return $object !== null
        && ($this->getClassName() === $object->getClassName() || $object instanceof GenericAggregateId)
        && $this->value() === $object->value()
        && $this->aggregateClass() === $object->aggregateClass();
    }

    final public function toString()
    {
        return Objects::toStringHelper($this)
            ->add($this->value())
            ->add($this->aggregateClass())
            ->toString();
    }
}
