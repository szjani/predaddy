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

use precore\lang\ObjectInterface;
use precore\util\Objects;
use predaddy\domain\AbstractDomainEvent;

/**
 * @package predaddy\fixture\article
 *
 * @author Szurovecz János <szjani@szjani.hu>
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

    public function equals(ObjectInterface $object = null)
    {
        return $object instanceof self
            && $this->author === $object->author
            && $this->text === $object->text;
    }

    public function toString()
    {
        return Objects::toStringHelper($this)
            ->add('author', $this->author)
            ->add('text', $this->text)
            ->toString();
    }
}
