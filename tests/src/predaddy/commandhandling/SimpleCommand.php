<?php
declare(strict_types=1);

namespace predaddy\commandhandling;

use predaddy\fixture\article\EventSourcedArticle;

class SimpleCommand extends AbstractCommand implements DirectCommand
{
    public $content;

    /**
     * @return string
     */
    public function aggregateClass() : string
    {
        return EventSourcedArticle::className();
    }
}
