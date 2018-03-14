<?php
declare(strict_types=1);

namespace predaddy\inmemory;

use predaddy\domain\AbstractDomainEvent;
use predaddy\domain\AggregateId;
use predaddy\domain\GenericAggregateId;
use predaddy\domain\DomainEvent;
use predaddy\domain\DomainTestCase;
use predaddy\domain\eventsourcing\CreateEventSourcedUser;
use predaddy\domain\eventsourcing\EventSourcedUser;
use predaddy\domain\eventsourcing\Increment;
use predaddy\fixture\article\ArticleCreated;
use predaddy\fixture\article\EventSourcedArticle;
use predaddy\fixture\article\EventSourcedArticleId;
use predaddy\fixture\article\TextChanged;

/**
 * @package predaddy\domain\impl
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class InMemoryEventStoreTest extends DomainTestCase
{
    /**
     * @var InMemoryEventStore
     */
    private $eventStore;

    protected function setUp()
    {
        parent::setUp();
        $this->eventStore = new InMemoryEventStore();
    }

    public function test()
    {
        $user = new EventSourcedUser(new CreateEventSourcedUser());
        $user->increment(new Increment());
        $events = $this->getAndClearRaisedEvents();
        foreach ($events as $event) {
            $this->eventStore->persist($event);
        }
        $returnedEvents = $this->eventStore->getEventsFor($user->getId());
        self::assertCount(2, $returnedEvents);
        self::assertEquals($events, $returnedEvents);

        $this->eventStore->createSnapshot($user->getId());
        $returnedSnapshot = $this->eventStore->loadSnapshot($user->getId());
        self::assertEquals($user, $returnedSnapshot);

        $user->increment(new Increment());
        $events = $this->getAndClearRaisedEvents();
        foreach ($events as $event) {
            $this->eventStore->persist($event);
        }
        $returnedEvents = $this->eventStore->getEventsFor(
            $user->getId(),
            $returnedSnapshot->stateHash()
        );
        self::assertCount(1, $returnedEvents);
        self::assertSame($events[0], $returnedEvents[0]);
    }

    public function testClean()
    {
        $user = new EventSourcedUser(new CreateEventSourcedUser());
        $events = $this->getAndClearRaisedEvents();
        foreach ($events as $event) {
            $this->eventStore->persist($event);
        }
        $this->eventStore->createSnapshot($user->getId());

        self::assertCount(1, $this->eventStore->getEventsFor($user->getId()));
        self::assertNotNull($this->eventStore->loadSnapshot($user->getId()));
        $this->eventStore->clean();
        self::assertCount(0, $this->eventStore->getEventsFor($user->getId()));
        self::assertNull($this->eventStore->loadSnapshot($user->getId()));
    }

    public function testAutoSnapshotting()
    {
        $strategy = $this->getMockBuilder('\predaddy\domain\eventsourcing\SnapshotStrategy')->getMock();
        $strategy
            ->expects(self::exactly(3))
            ->method('snapshotRequired')
            ->will(
                self::returnCallback(
                    function (DomainEvent $event, $version) {
                        return $version % 3 === 0;
                    }
                )
            );
        $eventStore = new InMemoryEventStore($strategy);

        $aggregateId = EventSourcedArticleId::create();
        $eventStore->persist(new ArticleCreated($aggregateId, 'author', 'text'));
        self::assertNull($eventStore->loadSnapshot($aggregateId));
        $eventStore->persist(self::anyArticleEvent($aggregateId));
        self::assertNull($eventStore->loadSnapshot($aggregateId));
        $eventStore->persist(self::anyArticleEvent($aggregateId));
        self::assertInstanceOf(EventSourcedArticle::className(), $eventStore->loadSnapshot($aggregateId));
    }

    private static function anyArticleEvent(AggregateId $aggregateId)
    {
        return AbstractDomainEvent::initEvent(new TextChanged('newText1'), $aggregateId, null);
    }

    /**
     * @test
     */
    public function shouldIgnoreNonEventSourcedAggregateIdInSnapshotting()
    {
        $aggregateId = new GenericAggregateId('value', __CLASS__);
        $this->eventStore->createSnapshot($aggregateId);
        self::assertNull($this->eventStore->loadSnapshot($aggregateId));
    }

    /**
     * @test
     */
    public function shouldNotCreateSnapshotIfThereAreNoEvents()
    {
        $aggregateId = EventSourcedArticleId::create();
        $this->eventStore->createSnapshot($aggregateId);
        self::assertNull($this->eventStore->loadSnapshot($aggregateId));
    }
}
