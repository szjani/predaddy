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
use predaddy\domain\EventSourcedUser;

require_once __DIR__ . '/../../EventSourcedUser.php';
require_once __DIR__ . '/../../IncrementedEvent.php';
require_once __DIR__ . '/../../UserCreated.php';

class DoctrineOrmEventStoreTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var DoctrineOrmEventStore
     */
    private $eventStorage;

    protected function setUp()
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
        $this->entityManager =  EntityManager::create($connectionOptions, $config);

        $schemaTool = new SchemaTool($this->entityManager);

        $cmf = $this->entityManager->getMetadataFactory();
        $classes = $cmf->getAllMetadata();

        $schemaTool->dropDatabase();
        $schemaTool->createSchema($classes);
        $this->eventStorage = new DoctrineOrmEventStore($this->entityManager);
    }

    protected function tearDown()
    {
        $this->entityManager = null;
    }

    public function testEventSave()
    {
        $raisedEvents = array();
        /* @var $aggregateId AggregateId */
        $aggregateId = null;
        $user = null;
        $eventStorage = $this->eventStorage;
        $this->entityManager->transactional(
            function () use ($eventStorage, &$aggregateId, &$user, &$raisedEvents) {
                $user = new EventSourcedUser();
                $user->increment();
                $aggregateId = $user->getId();
                $raisedEvents = $user->getAndClearRaisedEvents();
                $eventStorage->saveChanges(EventSourcedUser::className(), $raisedEvents, 0);
            }
        );
        $this->entityManager->clear();

        self::assertCount(2, $raisedEvents);
        $storedEvents = $this->eventStorage->getEventsFor(EventSourcedUser::className(), $aggregateId);
        self::assertEquals($raisedEvents[0], $storedEvents->current());
        $storedEvents->next();
        self::assertEquals($raisedEvents[1], $storedEvents->current());

        $user2 = EventSourcedUser::createEmpty();
        $user2->loadFromHistory($this->eventStorage->getEventsFor(EventSourcedUser::className(), $aggregateId));
        self::assertEquals($user->value, $user2->value);
    }

    public function testSnapshottingOutsideTransaction()
    {
        $aggregateId = null;
        $eventStorage = $this->eventStorage;
        $this->entityManager->transactional(
            function () use ($eventStorage, &$aggregateId) {
                $user = new EventSourcedUser();
                $user->increment();
                $aggregateId = $user->getId();
                $eventStorage->saveChanges(EventSourcedUser::className(), $user->getAndClearRaisedEvents(), 0);
            }
        );

        $events = $eventStorage->getEventsFor(EventSourcedUser::className(), $aggregateId);
        self::assertCount(2, $events);

        $this->entityManager->transactional(
            function () use ($eventStorage, $aggregateId) {
                $eventStorage->createSnapshot(EventSourcedUser::className(), $aggregateId);
            }
        );
        $events = $eventStorage->getEventsFor(EventSourcedUser::className(), $aggregateId);
        self::assertCount(0, $events);

        $aggregateRoot = $eventStorage->loadSnapshot(EventSourcedUser::className(), $aggregateId);
        self::assertEquals(EventSourcedUser::DEFAULT_VALUE + 1, $aggregateRoot->value);
    }

    public function testSnapshottingInsideTransaction()
    {
        $aggregateId = null;
        $eventStorage = $this->eventStorage;
        /* @var $user EventSourcedUser */
        $user = null;
        $this->entityManager->transactional(
            function () use ($eventStorage, &$aggregateId, &$user) {
                $user = new EventSourcedUser();
                $user->increment();
                $aggregateId = $user->getId();
                $eventStorage->saveChanges(EventSourcedUser::className(), $user->getAndClearRaisedEvents(), 0);
            }
        );

        $this->entityManager->transactional(
            function () use ($eventStorage, &$aggregateId, &$user) {
                $user->increment();
                $eventStorage->saveChanges(EventSourcedUser::className(), $user->getAndClearRaisedEvents(), 1);
                $eventStorage->createSnapshot(EventSourcedUser::className(), $aggregateId);
            }
        );

        $events = $eventStorage->getEventsFor(EventSourcedUser::className(), $aggregateId);
        self::assertCount(1, $events);

        $aggregateRoot = $eventStorage->loadSnapshot(EventSourcedUser::className(), $aggregateId);
        self::assertEquals(EventSourcedUser::DEFAULT_VALUE + 1, $aggregateRoot->value);
    }
}
