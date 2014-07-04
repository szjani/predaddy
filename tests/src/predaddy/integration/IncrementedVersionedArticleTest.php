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

namespace predaddy\integration;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use PHPUnit_Framework_TestCase;
use predaddy\domain\EventPublisher;
use predaddy\domain\impl\doctrine\DoctrineOrmEventStore;
use predaddy\domain\TrivialSnapshotStrategy;
use predaddy\eventhandling\EventBus;
use predaddy\eventhandling\EventFunctionDescriptorFactory;
use predaddy\fixture\article\ArticleCreated;
use predaddy\fixture\article\ArticleId;
use predaddy\fixture\article\IncrementedVersionedArticle;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\interceptors\EventPersister;

/**
 * @package predaddy\integration
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class IncrementedVersionedArticleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @var EntityManager
     */
    private static $entityManager;

    public static function setUpBeforeClass()
    {
        $isDevMode = true;
        $config = Setup::createAnnotationMetadataConfiguration(
            [
                __DIR__ . '/../../../../src/predaddy/domain/impl/doctrine',
                __DIR__ . '/../fixture/article'
            ],
            $isDevMode,
            '/tmp',
            null,
            false
        );

        $connectionOptions = ['driver' => 'pdo_sqlite', 'memory' => true];

        // obtaining the entity manager
        self::$entityManager =  EntityManager::create($connectionOptions, $config);

        $schemaTool = new SchemaTool(self::$entityManager);

        $cmf = self::$entityManager->getMetadataFactory();
        $classes = $cmf->getAllMetadata();

        $schemaTool->dropDatabase();
        $schemaTool->createSchema($classes);
    }

    protected function setUp()
    {
        $eventStore = new DoctrineOrmEventStore(self::$entityManager, TrivialSnapshotStrategy::$ALWAYS);
        $this->eventBus = new EventBus(
            new AnnotatedMessageHandlerDescriptorFactory(
                new EventFunctionDescriptorFactory()
            ),
            [new EventPersister($eventStore)]
        );
        EventPublisher::instance()->setEventBus($this->eventBus);
    }

    public function testCreation()
    {
        $eventRaised = false;
        $author = 'David';
        $text = 'Hello World!';
        $articleId = null;
        /* @var $articleId ArticleId */
        $this->eventBus->registerClosure(
            function (ArticleCreated $event) use ($author, $text, &$eventRaised, &$articleId) {
                self::assertEquals($author, $event->getAuthor());
                self::assertEquals($text, $event->getText());
                $articleId = $event->aggregateId();
                $eventRaised = true;
            }
        );

        self::$entityManager->transactional(
            function () use ($author, $text) {
                $article = new IncrementedVersionedArticle($author, $text);
                self::$entityManager->persist($article);
            }
        );
        self::assertTrue($eventRaised);

        self::$entityManager->transactional(
            function () use ($articleId) {
                $res = self::$entityManager->find(IncrementedVersionedArticle::className(), $articleId->value());
                self::assertInstanceOf(IncrementedVersionedArticle::className(), $res);
                /* @var $res IncrementedVersionedArticle */
                self::assertEquals(1, $res->stateHash());
                self::assertTrue($articleId->equals($res->getId()));
                $res->changeText('newText');
            }
        );
    }
}
