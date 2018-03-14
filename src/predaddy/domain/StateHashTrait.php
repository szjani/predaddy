<?php
declare(strict_types=1);

namespace predaddy\domain;

/**
 * Trait for {@link StateHashAware} implementations.
 *
 * @package predaddy\domain
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
trait StateHashTrait
{
    protected $stateHash;

    /**
     * @return null|string
     */
    public function stateHash() : ?string
    {
        return $this->stateHash;
    }
}
