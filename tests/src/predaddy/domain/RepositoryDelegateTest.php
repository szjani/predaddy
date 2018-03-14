<?php
declare(strict_types=1);

namespace predaddy\domain;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_TestCase;
use predaddy\fixture\article\EventSourcedArticle;
use predaddy\fixture\article\EventSourcedArticleId;

class RepositoryDelegateTest extends TestCase
{
    private $anArticle;
    private $delegate;
    private $repo;

    protected function setUp()
    {
        $this->anArticle = new EventSourcedArticle('author', 'text');
        $this->repo = $this->getMockBuilder('\predaddy\domain\Repository')->getMock();
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
