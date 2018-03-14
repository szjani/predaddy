<?php
declare(strict_types=1);

namespace predaddy\fixture\article;

use predaddy\domain\AbstractDomainEvent;

/**
 * @package predaddy\fixture\article
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class TextChanged extends AbstractDomainEvent
{
    private $newText;

    public function __construct($newText)
    {
        parent::__construct();
        $this->newText = $newText;
    }

    /**
     * @return mixed
     */
    public function getNewText()
    {
        return $this->newText;
    }
}
