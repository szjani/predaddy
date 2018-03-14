<?php
declare(strict_types=1);

namespace predaddy\fixture\article;

use predaddy\commandhandling\AbstractCommand;
use predaddy\commandhandling\DirectCommand;
use predaddy\domain\StateHashAware;
use predaddy\domain\StateHashTrait;

/**
 * @package src\predaddy\fixture
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ChangeText extends AbstractCommand implements DirectCommand, StateHashAware
{
    use StateHashTrait;

    private $newText;

    public function __construct($aggregateId, $stateHash, $newText)
    {
        parent::__construct($aggregateId);
        $this->stateHash = $stateHash;
        $this->newText = $newText;
    }

    /**
     * @return null|string
     */
    public function getNewText()
    {
        return $this->newText;
    }

    /**
     * @return string
     */
    public function aggregateClass() : string
    {
        return EventSourcedArticle::className();
    }
}
