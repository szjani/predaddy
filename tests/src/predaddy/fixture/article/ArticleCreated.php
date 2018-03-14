<?php
declare(strict_types=1);

namespace predaddy\fixture\article;

use precore\lang\ObjectInterface;
use precore\util\Objects;
use predaddy\domain\AbstractDomainEvent;

/**
 * @package predaddy\fixture\article
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ArticleCreated extends AbstractDomainEvent
{
    private $author;
    private $text;

    /**
     * @param ArticleId $articleId
     * @param $author
     * @param $text
     */
    public function __construct(ArticleId $articleId, $author, $text)
    {
        parent::__construct($articleId);
        $this->author = $author;
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    public function toString() : string
    {
        return Objects::toStringHelper($this)
            ->add('author', $this->author)
            ->add('text', $this->text)
            ->toString();
    }
}
