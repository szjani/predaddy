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

namespace predaddy\domain\impl\doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use PHPUnit_Framework_TestCase;
use predaddy\domain\EventPublisher;
use predaddy\domain\impl\doctrine\entities\IncrementedEvent;
use predaddy\domain\impl\doctrine\entities\UnversionedUser;
use predaddy\domain\impl\doctrine\entities\User;
use predaddy\domain\impl\doctrine\entities\UserCreated;
use predaddy\eventhandling\EventBus;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\DefaultFunctionDescriptorFactory;
use predaddy\messagehandling\interceptors\BlockerInterceptor;

/**
 * @package predaddy\domain\impl\doctrine
 *
 * @author Szurovecz János <szjani@szjani.hu>
 * @group integration
 */
class DoctrineAggregateRootRepositoryIntegrationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var BlockerInterceptor
     */
    private $blockerInterceptor;

    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @var EntityManager
     */
    private static $entityManager;

    /**
     * @var DoctrineAggregateRootRepository
     */
    private $userRepo;

    /**
     * @var DoctrineAggregateRootRepository
     */
    private $unversionedUserRepo;

    public static function setUpBeforeClass()
    {
        $isDevMode = true;
        $config = Setup::createAnnotationMetadataConfiguration(
            [__DIR__ . '/entities'],
            $isDevMode,
            null,
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
        $this->blockerInterceptor = new BlockerInterceptor();
        $this->eventBus = new EventBus(
            new AnnotatedMessageHandlerDescriptorFactory(new DefaultFunctionDescriptorFactory()),
            [$this->blockerInterceptor]
        );
        EventPublisher::instance()->setEventBus($this->eventBus);
        $this->userRepo = new DoctrineAggregateRootRepository(self::$entityManager);
        $this->unversionedUserRepo = new DoctrineAggregateRootRepository(self::$entityManager);
    }

    /**
     * @test
     */
    public function checkVersionAndAggregateIdSet()
    {
        $called = 0;
        /* @var $user User */
        $user = null;
        $this->blockerInterceptor->startBlocking();
        $this->eventBus->registerClosure(
            function (UserCreated $event) use (&$called, &$user) {
                PHPUnit_Framework_TestCase::assertEquals($user->stateHash(), $event->stateHash());
                PHPUnit_Framework_TestCase::assertTrue(
                    $event->aggregateId()->equals($user->getId())
                );
                $called++;
            }
        );
        $user = new User();
        $this->userRepo->save($user);
        $this->blockerInterceptor->flush();
        self::assertEquals(1, $called);

        // next transaction
        $this->eventBus->registerClosure(
            function (IncrementedEvent $event) use (&$called, &$user) {
                PHPUnit_Framework_TestCase::assertEquals($user->stateHash(), $event->stateHash());
                PHPUnit_Framework_TestCase::assertTrue(
                    $event->aggregateId()->equals($user->getId())
                );
                $called++;
            }
        );

        $user->increment();
        $this->userRepo->save($user);
        $this->blockerInterceptor->flush();
        self::assertEquals(2, $called);
    }

    public function testUnversionedUser()
    {
        $called = 0;
        $user = null;
        $this->eventBus->registerClosure(
            function (UserCreated $event) use (&$called, &$user) {
                PHPUnit_Framework_TestCase::assertEquals($user->stateHash(), $event->stateHash());
                PHPUnit_Framework_TestCase::assertTrue(
                    $event->aggregateId()->equals($user->getId())
                );
                $called++;
            }
        );
        $this->blockerInterceptor->startBlocking();
        $user = new UnversionedUser();
        $this->unversionedUserRepo->save($user);
        $this->blockerInterceptor->flush();
        self::assertEquals(1, $called);

        // next transaction
        $this->eventBus->registerClosure(
            function (IncrementedEvent $event) use (&$called, &$user) {
                PHPUnit_Framework_TestCase::assertEquals($user->stateHash(), $event->stateHash());
                PHPUnit_Framework_TestCase::assertTrue(
                    $event->aggregateId()->equals($user->getId())
                );
                $called++;
            }
        );

        $user->increment();
        $this->unversionedUserRepo->save($user);
        $this->blockerInterceptor->flush();
        self::assertEquals(2, $called);
    }
}
