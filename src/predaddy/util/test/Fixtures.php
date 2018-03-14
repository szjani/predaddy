<?php
declare(strict_types=1);

namespace predaddy\util\test;

use precore\lang\ObjectClass;
use precore\util\Preconditions;
use predaddy\domain\Repository;

/**
 * Utility class which makes easier to test aggregates. Supports both ES and non-ES aggregates.
 * Follows BDD concept:
 * <ul>
 *   <li>init commands or events initialize the AR
 *   <li>the required command should be sent
 *   <li>the expected events, return values, or thrown exception can be checked
 * </ul>
 *
 * It uses PHPUnit, which must be available.
 *
 * {@link Fixture} helps to fill commands with aggregate ID, and ignore some properties when compares events.
 * It is convenience if the aggregate ID is generated inside the AR, and further commands need to know it.
 * This feature works only with {@link AbstractCommand} and {@link AbstractDomainEvent} objects.
 *
 * @beta This, and all related test utility classes are under development and can be modified anytime.
 * @package predaddy\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Fixtures
{
    private function __construct()
    {
    }

    /**
     * @param string $aggregateClass which should be tested
     * @param Repository $repository for DirectCommandBus only in non-ES case
     * @return Fixture
     */
    public static function newGivenWhenThenFixture($aggregateClass, Repository $repository = null)
    {
        $arClassObject = ObjectClass::forName($aggregateClass);
        Preconditions::checkArgument(
            ObjectClass::forName('\predaddy\domain\AggregateRoot')->isAssignableFrom($arClassObject),
            'Aggregate class [%s] does not implement AggregateRoot interface',
            $aggregateClass
        );
        $eventSourcedClass = ObjectClass::forName('\predaddy\domain\eventsourcing\EventSourcedAggregateRoot');
        return $eventSourcedClass->isAssignableFrom($arClassObject)
            ? new EventSourcedFixture($aggregateClass)
            : new CommandSourcedFixture($aggregateClass, $repository);
    }
}
