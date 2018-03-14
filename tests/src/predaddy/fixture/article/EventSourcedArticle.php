<?php
declare(strict_types=1);

namespace predaddy\fixture\article;

use predaddy\domain\AggregateId;
use predaddy\domain\eventsourcing\AbstractEventSourcedAggregateRoot;
use predaddy\messagehandling\annotation\Subscribe;

/**
 * @package predaddy\fixture\article
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class EventSourcedArticle extends AbstractEventSourcedAggregateRoot
{
    /**
     * @var EventSourcedArticleId
     */
    private $articleId;

    /**
     * @var string
     */
    private $author;

    /**
     * @var string
     */
    private $text;

    /**
     * @param $author
     * @param $text
     */
    public function __construct($author, $text)
    {
        $this->apply(new ArticleCreated(EventSourcedArticleId::create(), $author, $text));
    }

    /**
     * @Subscribe
     * @param ChangeText $command
     */
    public function changeText(ChangeText $command)
    {
        $this->apply(new TextChanged($command->getNewText()));
    }

    /**
     * @return EventSourcedArticleId
     */
    public function getId() : AggregateId
    {
        return $this->articleId;
    }

    /**
     * @Subscribe
     * @param TextChanged $event
     */
    private function handleTextChanged(TextChanged $event)
    {
        $this->text = $event->getNewText();
    }

    /**
     * @Subscribe
     * @param ArticleCreated $event
     */
    private function handleArticleCreated(ArticleCreated $event)
    {
        $this->articleId = $event->aggregateId();
        $this->author = $event->getAuthor();
        $this->text = $event->getText();
    }
}
