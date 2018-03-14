<?php
declare(strict_types=1);

namespace predaddy\commandhandling;

use precore\util\ToStringHelper;
use predaddy\domain\StateHashAware;
use predaddy\messagehandling\AbstractMessage;

/**
 * Base class for all types of commands.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class AbstractCommand extends AbstractMessage implements Command
{
    /**
     * @var string|null
     */
    protected $aggregateId;

    /**
     * @param string|null $aggregateId
     */
    public function __construct(string $aggregateId = null)
    {
        parent::__construct();
        $this->aggregateId = $aggregateId;
    }

    /**
     * @return null|string
     */
    public function aggregateId() : ?string
    {
        return $this->aggregateId;
    }

    protected function toStringHelper() : ToStringHelper
    {
        $toStringHelper = parent::toStringHelper()->add('aggregateId', $this->aggregateId);
        if ($this instanceof StateHashAware) {
            $toStringHelper->add('stateHash', $this->stateHash());
        }
        if ($this instanceof DirectCommand) {
            $toStringHelper->add('aggregateClass', $this->aggregateClass());
        }
        return $toStringHelper;
    }
}
