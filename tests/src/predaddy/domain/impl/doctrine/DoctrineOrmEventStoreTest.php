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
                foreach ($raisedEvents as $event) {
                    $eventStorage->persist($event);
                }
            }
        );
        self::$entityManager->clear();

        self::assertCount(2, $raisedEvents);
        $storedEvents = $this->eventStore->getEventsFor($aggregateId);
        self::assertEquals($raisedEvents[0], $storedEvents->current());
        $storedEvents->next();
        self::assertEquals($raisedEvents[1], $storedEvents->current());

        $user2 = EventSourcedUser::objectClass()->newInstanceWithoutConstructor();
        $user2->loadFromHistory($this->eventStore->getEventsFor($aggregateId));
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
                foreach ($this->getAndClearRaisedEvents() as $event) {
                    $eventStorage->persist($event);
                }
            }
        );

        $events = $eventStorage->getEventsFor($aggregateId);
        self::assertCount(2, $events);

        self::$entityManager->transactional(
            function () use ($eventStorage, $aggregateId) {
                $eventStorage->createSnapshot($aggregateId);
            }
        );
        $events = $eventStorage->getEventsFor($aggregateId, $events[1]->stateHash());
        self::assertCount(0, $events);

        $aggregateRoot = $eventStorage->loadSnapshot($aggregateId);
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
                foreach ($this->getAndClearRaisedEvents() as $event) {
                    $eventStore->persist($event);
                }
            }
        );

        self::$entityManager->transactional(
            function () use ($eventStore, &$aggregateId, &$user) {
                $user->increment(new Increment($aggregateId, 1));
                foreach ($this->getAndClearRaisedEvents() as $event) {
                    $eventStore->persist($event);
                }
                $eventStore->createSnapshot($aggregateId);
            }
        );

        $user = $eventStore->loadSnapshot($aggregateId);
        $events = $eventStore->getEventsFor($aggregateId, $user->stateHash());
        self::assertCount(1, $events);

        $aggregateRoot = $eventStore->loadSnapshot($aggregateId);
        self::assertEquals(EventSourcedUser::DEFAULT_VALUE + 1, $aggregateRoot->value);
    }

    /**
     * @test
     */
    public function testSnapshotting()
    {
        $aggregateId = null;
        /* @var $user EventSourcedUser */
        $user = null;
        $raisedEvents = [];
        self::$entityManager->transactional(
            function () use (&$aggregateId, &$user, &$raisedEvents) {
                $user = new EventSourcedUser(new CreateEventSourcedUser());
                $user->increment(new Increment($aggregateId));
                $aggregateId = $user->getId();
                $raisedEvents = $this->getAndClearRaisedEvents();
                foreach ($raisedEvents as $event) {
                    $this->eventStore->persist($event);
                }
            }
        );
        self::assertEquals(2, count($raisedEvents));
        self::$entityManager->transactional(
            function () use ($aggregateId) {
                $this->eventStore->createSnapshot($aggregateId);
            }
        );
        self::assertEquals($raisedEvents[1]->stateHash(), $user->stateHash());
        $reducedEvents = $this->eventStore->getEventsFor(
            $aggregateId,
            $raisedEvents[0]->stateHash()
        );
        self::assertCount(1, $reducedEvents);
    }
}
