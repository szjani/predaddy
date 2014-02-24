<?php
/*
 * Copyright (c) 2014 Szurovecz János
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
use predaddy\domain\AggregateId;
use predaddy\domain\AggregateRoot;
use predaddy\domain\DomainEvent;
use predaddy\domain\EventSourcedUser;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\event\EventBus;
use predaddy\messagehandling\event\EventFunctionDescriptorFactory;
use trf4php\doctrine\DoctrineTransactionManager;

require_once __DIR__ . '/../../EventSourcedUser.php';
require_once __DIR__ . '/../../IncrementedEvent.php';
require_once __DIR__ . '/../../UserCreated.php';

class DoctrineOrmEventStorageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    protected static $entityManager;

    /**
     * @var DoctrineOrmEventStorage
     */
    private $eventStorage;

    /**
     * @var EventBus
     */
    private $eventBus;

    public static function setUpBeforeClass()
    {
        $isDevMode = true;
        $config = Setup::createAnnotationMetadataConfiguration(
            array(str_replace('/tests', '', __DIR__)),
            $isDevMode,
            null,
            null,
            false
        );

        $connectionOptions = array('driver' => 'pdo_sqlite', 'memory' => true);

        // obtaining the entity manager
        self::$entityManager =  EntityManager::create($connectionOptions, $config);

        $schemaTool = new SchemaTool(self::$entityManager);

        $cmf = self::$entityManager->getMetadataFactory();
        $classes = $cmf->getAllMetadata();

        $schemaTool->dropDatabase();
        $schemaTool->createSchema($classes);
    }

    public static function tearDownAfterClass()
    {
        self::$entityManager = null;
    }

    public function setUp()
    {
        $this->eventStorage = new DoctrineOrmEventStorage(self::$entityManager);
        $functionDescFactory = new EventFunctionDescriptorFactory();
        $handlerDescFactory = new AnnotatedMessageHandlerDescriptorFactory($functionDescFactory);
        $transactionManager = new DoctrineTransactionManager(self::$entityManager);
        $this->eventBus = new EventBus($handlerDescFactory, $transactionManager);
        AggregateRoot::setEventBus($this->eventBus);
    }

    public function testEventSave()
    {
        $raisedEvents = array();
        $this->eventBus->registerClosure(
            function (DomainEvent $event) use (&$raisedEvents) {
                $raisedEvents[] = $event;
            }
        );
        /* @var $aggregateId AggregateId */
        $aggregateId = null;
        $user = null;
        $eventStorage = $this->eventStorage;
        self::$entityManager->transactional(
            function () use ($eventStorage, &$aggregateId, &$user) {
                $user = new EventSourcedUser();
                $user->increment();
                $aggregateId = $user->getId();
                $eventStorage->saveChanges($aggregateId, $user->getAndClearRaisedEvents(), 0, $user);
            }
        );
        self::$entityManager->clear();

        self::assertCount(2, $raisedEvents);
        $storedEvents = $this->eventStorage->getEventsFor($aggregateId);
        self::assertEquals($raisedEvents[0], $storedEvents->current());
        $storedEvents->next();
        self::assertEquals($raisedEvents[1], $storedEvents->current());

        $user2 = EventSourcedUser::createEmpty();
        $user2->loadFromHistory($this->eventStorage->getEventsFor($aggregateId));
        self::assertEquals($user->value, $user2->value);

        $filteredEvents = $this->eventStorage->getEventsFor($aggregateId, 1);
        self::assertEquals($raisedEvents[1], $filteredEvents->current());
        $filteredEvents->next();
        self::assertFalse($filteredEvents->valid());
    }
}
