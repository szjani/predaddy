<?php
declare(strict_types=1);

namespace predaddy\domain;

use precore\lang\IllegalStateException;

/**
 * Aggregate root class which provides the AggregateId
 * and the ability to validate its state.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface AggregateRoot extends Entity, StateHashAware
{
    /**
     * @return AggregateId
     */
    public function getId() : AggregateId;

    /**
     * Validates the state of the aggregate. Should be called from command handlers
     * if the expected state in the command is not null.
     *
     * Helps to detect lost update problem.
     *
     * @see http://www.w3.org/1999/04/Editing
     * @param string $expectedHash
     * @throws IllegalStateException
     */
    public function failWhenStateHashViolation($expectedHash) : void;
}
