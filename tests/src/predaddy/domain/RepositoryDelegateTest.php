<?php
/*
 * Copyright (c) 2012-2014 Janos Szurovecz
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

namespace predaddy\domain;

use PHPUnit_Framework_TestCase;
use predaddy\fixture\article\EventSourcedArticle;
use predaddy\fixture\article\EventSourcedArticleId;

class RepositoryDelegateTest extends PHPUnit_Framework_TestCase
{
    private $anArticle;
    private $delegate;
    private $repo;

    protected function setUp()
    {
        $this->anArticle = new EventSourcedArticle('author', 'text');
        $this->repo = $this->getMock('\predaddy\domain\Repository');
        $this->delegate = new RepositoryDelegate([EventSourcedArticle::className() => $this->repo]);
    }

    /**
     * @test
     */
    public function shouldFindRegisteredRepositoryForLoad()
    {
        $aggregateId = EventSourcedArticleId::create();
        $this->repo
            ->expects(self::once())
            ->method('load')
            ->with($aggregateId);
        $this->delegate->load($aggregateId);
    }

    /**
     * @test
     */
    public function shouldFindRegisteredRepositoryForSave()
    {
        $this->repo
            ->expects(self::once())
            ->method('save')
            ->with($this->anArticle);

        $this->delegate->save($this->anArticle);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfNoRegisteredRepository()
    {
        $delegate = new RepositoryDelegate([]);
        $delegate->load(EventSourcedArticleId::create());
    }
}
