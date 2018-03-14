<?php
declare(strict_types=1);

namespace predaddy\domain;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface StateHashAware
{
    /**
     * @return null|string
     */
    public function stateHash() : ?string;
}
