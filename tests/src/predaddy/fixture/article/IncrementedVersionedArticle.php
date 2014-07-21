<?php
/*
 * Copyright (c) 2012-2014 Szurovecz János
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace predaddy\fixture\article;

use predaddy\domain\AbstractAggregateRoot;
use predaddy\domain\AggregateId;
use predaddy\domain\DomainEvent;
use Doctrine\ORM\Mapping as ORM;

/**
 * @package predaddy\fixture
 *
 * @author Szurovecz János <szjani@szjani.hu>
 *
 * @ORM\Entity
 * @ORM\Table(name="article")
 */
class IncrementedVersionedArticle extends AbstractAggregateRoot
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", name="id")
     * @var string
     */
    private $articleId;

    /**
     * @ORM\Column(type="string", name="author")
     * @var string
     */
    private $author;

    /**
     * @ORM\Column(type="string", name="text")
     * @var string
     */
    private $text;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $stateHash = 0;

    /**
     * Used for optimistic locking by Doctrine.
     *
     * @ORM\Column(type="integer")
     * @ORM\Version
     * @var int
     */
    private $version;

    /**
     * @param $author
     * @param $text
     */
    public function __construct($author, $text)
    {
        $articleId = ArticleId::create();
        $this->articleId = $articleId->value();
        $this->author = $author;
        $this->text = $text;
        $this->raise(new ArticleCreated($articleId, $author, $text));
    }

    /**
     * @return AggregateId
     */
    public function getId()
    {
        return ArticleId::from($this->articleId);
    }

    public function changeText($newText)
    {
        $this->text = $newText;
        $this->raise(new TextChanged($newText));
    }

    protected function calculateNextStateHash(DomainEvent $raisedEvent)
    {
        return $this->stateHash + 1;
    }

    protected function setStateHash($stateHash)
    {
        $this->stateHash = $stateHash;
    }

    public function stateHash()
    {
        return $this->stateHash;
    }
}
