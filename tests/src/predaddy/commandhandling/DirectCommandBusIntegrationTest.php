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

namespace predaddy\commandhandling;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use PHPUnit_Framework_TestCase;
use predaddy\domain\AggregateId;
use predaddy\domain\CreateEventSourcedUser;
use predaddy\domain\impl\doctrine\DoctrineOrmEventStore;
use predaddy\domain\impl\LazyEventSourcedRepositoryRepository;
use predaddy\domain\Increment;
use predaddy\domain\IncrementedEvent;
use predaddy\domain\TrivialSnapshotStrategy;
use predaddy\domain\UserCreated;
use predaddy\eventhandling\EventBus;
use predaddy\eventhandling\EventFunctionDescriptorFactory;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use trf4php\doctrine\DoctrineTransactionManager;

require_once __DIR__ . '/../domain/EventSourcedUser.php';
require_once __DIR__ . '/../domain/IncrementedEvent.php';
require_once __DIR__ . '/../domain/UserCreated.php';
require_once __DIR__ . '/../domain/CreateEventSourcedUser.php';
require_once __DIR__ . '/../domain/Increment.php';

class DirectCommandBusIntegrationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @var CommandBus
     */
    private $commandBus;

    private static $entityManager;

    public static function setUpBeforeClass()
    {
        $isDevMode = true;
        $config = Setup::createAnnotationMetadataConfiguration(
            array(str_replace(DIRECTORY_SEPARATOR . 'tests', '', __DIR__ . '/../domain/impl/doctrine')),
            $isDevMode,
            '/tmp',
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

    protected function setUp()
    {
        $transactionManager = new DoctrineTransactionManager(self::$entityManager);
        $this->eventBus = new EventBus(
            new AnnotatedMessageHandlerDescriptorFactory(new EventFunctionDescriptorFactory()),
            $transactionManager
        );
        $eventStore = new DoctrineOrmEventStore(self::$entityManager);
        $this->commandBus = new DirectCommandBus(
            new AnnotatedMessageHandlerDescriptorFactory(new CommandFunctionDescriptorFactory()),
            $transactionManager,
            new LazyEventSourcedRepositoryRepository($this->eventBus, $eventStore, TrivialSnapshotStrategy::$NEVER)
        );
    }

    public function testIntegration()
    {
        /* @var $aggregateId AggregateId */
        $aggregateId = null;
        $this->eventBus->registerClosure(
            function (UserCreated $event) use (&$aggregateId) {
                $aggregateId = $event->getAggregateIdentifier();
            }
        );
        $this->commandBus->post(new CreateEventSourcedUser());
        self::assertNotNull($aggregateId);

        $incremented = false;
        $this->eventBus->registerClosure(
            function (IncrementedEvent $event) use (&$incremented, $aggregateId) {
                DirectCommandBusIntegrationTest::assertEquals($aggregateId, $event->getAggregateIdentifier());
                $incremented = true;
            }
        );
        $this->commandBus->post(new Increment($aggregateId->getValue(), 1));
        self::assertTrue($incremented);
    }
}
