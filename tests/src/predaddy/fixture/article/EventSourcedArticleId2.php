<?php
declare(strict_types=1);

namespace predaddy\fixture\article;

use predaddy\domain\UUIDAggregateId;

/**
 * @package predaddy\fixture
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class EventSourcedArticleId2 extends UUIDAggregateId implements ArticleId
{
    /**
     * @return string FQCN
     */
    public function aggregateClass() : string
    {
        return EventSourcedArticle::className();
    }
}
