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
use predaddy\domain\AggregateId;
use predaddy\domain\CreateEventSourcedUser;
use predaddy\domain\DomainTestCase;
use predaddy\domain\EventSourcedUser;
use predaddy\domain\Increment;

/**
 * Class DoctrineOrmEventStoreTest
 *
 * @package predaddy\domain\impl\doctrine
 *
 * @author Szurovecz János <szjani@szjani.hu>
 * @group integration
 */
class DoctrineOrmEventStoreTest extends DomainTestCase
{
    /**
     * @var EntityManager
     */
    private static $entityManager;

    /**
     * @var DoctrineOrmEventStore
     */
    private $eventStore;

    public static function setUpBeforeClass()
    {
        $isDevMode = true;
        $config = Setup::createAnnotationMetadataConfiguration(
            [str_replace(DIRECTORY_SEPARATOR . 'tests', '', __DIR__)],
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
        parent::setUp();
        $this->eventStore = new DoctrineOrmEventStore(self::$entityManager);
    }

    public function testEventSave()
    {
        $raisedEvents = [];
        /* @var $aggregateId AggregateId */
        $aggregateId = null;
        $user = null;
        $eventStorage = $this->eventStore;
        self::$entityManager->transactional(
            function () use ($eventStorage, &$aggregateId, &$user, &$raisedEvents) {
                $user = new EventSourcedUser(new CreateEventSourcedUser());
                $user->increment(new Increment());
                $aggregateId = $user->getId();
                $raisedEvents = $this->getAndClearRaisedEvents();
                $eventStorage->saveChanges(EventSourcedUser::className(), $raisedEvents);
            }
        );
        self::$entityManager->clear();

        self::assertCount(2, $raisedEvents);
        $storedEvents = $this->eventStore->getEventsFor(EventSourcedUser::className(), $aggregateId);
        self::assertEquals($raisedEvents[0], $storedEvents->current());
        $storedEvents->next();
        self::assertEquals($raisedEvents[1], $storedEvents->current());

        $user2 = EventSourcedUser::objectClass()->newInstanceWithoutConstructor();
        $user2->loadFromHistory($this->eventStore->getEventsFor(EventSourcedUser::className(), $aggregateId));
        self::assertEquals($user->value, $user2->value);
    }

    public function testSnapshottingOutsideTransaction()
    {
        $aggregateId = null;
        $eventStorage = $this->eventStore;
        self::$entityManager->transactional(
            function () use ($eventStorage, &$aggregateId) {
                $user = new EventSourcedUser(new CreateEventSourcedUser());
                $user->increment(new Increment());
                $aggregateId = $user->getId();
                $eventStorage->saveChanges(EventSourcedUser::className(), $this->getAndClearRaisedEvents());
            }
        );

        $events = $eventStorage->getEventsFor(EventSourcedUser::className(), $aggregateId);
        self::assertCount(2, $events);

        self::$entityManager->transactional(
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
        $eventStore = $this->eventStore;
        /* @var $user EventSourcedUser */
        $user = null;
        self::$entityManager->transactional(
            function () use ($eventStore, &$aggregateId, &$user) {
                $user = new EventSourcedUser(new CreateEventSourcedUser());
                $user->increment(new Increment($aggregateId));
                $aggregateId = $user->getId();
                $eventStore->saveChanges(EventSourcedUser::className(), $this->getAndClearRaisedEvents());
            }
        );

        self::$entityManager->transactional(
            function () use ($eventStore, &$aggregateId, &$user) {
                $user->increment(new Increment($aggregateId, 1));
                $eventStore->saveChanges(EventSourcedUser::className(), $this->getAndClearRaisedEvents());
                $eventStore->createSnapshot(EventSourcedUser::className(), $aggregateId);
            }
        );

        $events = $eventStore->getEventsFor(EventSourcedUser::className(), $aggregateId);
        self::assertCount(1, $events);

        $aggregateRoot = $eventStore->loadSnapshot(EventSourcedUser::className(), $aggregateId);
        self::assertEquals(EventSourcedUser::DEFAULT_VALUE + 1, $aggregateRoot->value);
    }
}
