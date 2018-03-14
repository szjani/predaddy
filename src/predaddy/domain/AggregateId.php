<?php
declare(strict_types=1);

namespace predaddy\domain;

/**
 * Represents the identifier and the type of an aggregate.
 * The aggregate id and type is defined by its aggregate root.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface AggregateId extends ValueObject
{
    /**
     * @return string
     */
    public function value() : string;

    /**
     * @return string FQCN
     */
    public function aggregateClass() : string;
}
